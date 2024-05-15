<?php

namespace Wanphp\Plugins\Exam\Application\Manage;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;
use Wanphp\Plugins\Exam\Application\Api;
use Wanphp\Plugins\Exam\Domain\ExamQuestionsInterface;

class ImportQuestionApi extends Api
{
  private ExamQuestionsInterface $questions;
  private string $filepath;

  /**
   * @param ContainerInterface $container
   * @param ExamQuestionsInterface $questions
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function __construct(ContainerInterface $container, ExamQuestionsInterface $questions)
  {
    $settings = $container->get('settings');
    $this->questions = $questions;
    $this->filepath = $settings['uploadFilePath'];
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $uploadedFiles = $this->request->getUploadedFiles();
    $uploadedFile = $uploadedFiles['file'];

    $post = $this->request->getParsedBody();
    $data = [
      'type' => $post['type'] ?? $uploadedFile->getClientMediaType(),
      'md5' => $post['md5'] ?? md5_file($uploadedFile->getFilePath()),
      'extension' => strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION))
    ];


    if (!in_array($data['extension'], array('xls', 'xlsx'))) {
      return $this->respondWithError('文件类型错误！');
    }

    if (!in_array($data['type'], [
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
      return $this->respondWithError('文件类型错误！');
    }

    $content = file_get_contents($uploadedFile->getFilePath());
    if (preg_match('/<\?php/i', $content)) {
      return $this->respondWithError('文件类型错误！');
    }

    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
      $filepath = '/tmp/';
      if (!is_dir($this->filepath . $filepath)) mkdir($this->filepath . $filepath, 0755, true);

      //大文件分块上传
      if (isset($post['chunks']) && $post['chunks'] > 0) {
        $tmpPath = $this->filepath . '/tmp/' . $data['md5'];//上传文件临时目录
        if ($post['current_chunk'] == 1 && is_dir($tmpPath)) { //断点续传
          //已上传数量
          $current_chunk = 1;
          $num = 1;
          while ($num > 0) {
            $cacheFile = $tmpPath . '/' . $num . '.dat';
            if (!file_exists($cacheFile)) {
              $current_chunk = $num - 1;//最后上传文件块
              $num = -1; //退出循环检查
            } else {
              $num++;
            }
          }

          if ($current_chunk >= $post['current_chunk'] && $current_chunk < $post['chunks']) {//继续上传
            $post['current_chunk'] = $current_chunk;
            return $this->respondWithData(['current_chunk' => $post['current_chunk'], 'msg' => '继续上传第' . ($post['current_chunk'] + 1) . '块文件！'], 202);
          }
        }

        //创建临时目录,上传文件块
        if (!is_dir($tmpPath)) mkdir($tmpPath, 0755, true);
        $this->moveUploadedFile($tmpPath, $uploadedFile, $post['current_chunk'] . '.dat');

        if ($post['current_chunk'] == $post['chunks']) {//最后一块,合成大文件
          $extension = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
          $basename = bin2hex(random_bytes(8));
          $filename = sprintf('%s.%0.8s', $basename, $extension);
          $file_path = $filepath . $filename;
          $fp = fopen($this->filepath . $file_path, "wb");
          $num = 1;
          while ($num > 0) {
            $cacheFile = $tmpPath . '/' . $num++ . '.dat';
            if (file_exists($cacheFile)) {
              $handle = fopen($cacheFile, 'rb');
              $content = fread($handle, filesize($cacheFile));
              fwrite($fp, $content);
              fclose($handle);
              unset($handle);
              unlink($cacheFile);//删除临时文件
            } else {
              $num = -1;
              rmdir($tmpPath);//删除目录
            }
          }
          fclose($fp);
          unset($fp);

          $file = $this->filepath . $file_path;
        } else {
          return $this->respondWithData(['current_chunk' => $post['current_chunk'], 'msg' => '第' . $post['current_chunk'] . '块文件上传成功！'], 202);
        }
      } else {
        $filename = $this->moveUploadedFile($this->filepath . $filepath, $uploadedFile);
        $file = $this->filepath . $filepath . $filename;
      }
      if ($file) {
        //读取表格数据并导入题库
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($data['extension']));
        $reader->setReadDataOnly(TRUE);
        $spreadsheet = $reader->load($file);

        $data = [];
        // 选择题
        $spreadsheet->setActiveSheetIndex(0);
        foreach ($spreadsheet->getActiveSheet()->toArray() as $item) {
          if (is_null($item[0])) break;
          $data[] = [
            'examId' => $this->args['id'],
            'question' => $item[0],
            'answerItem' => ['A、' . $item[1], 'B、' . $item[2], 'C、' . $item[3], 'D、' . $item[4]],
            'answer' => array_filter(str_split($item[5], 1), 'strlen'),
            'ctime' => time()
          ];
        };
        // 填空题目
        $spreadsheet->setActiveSheetIndex(1);
        foreach ($spreadsheet->getActiveSheet()->toArray() as $item) {
          if (is_null($item[0])) break;
          $data[] = [
            'examId' => $this->args['id'],
            'question' => $item[0],
            'orderly' => $item[1] == '是' ? 1 : 0,
            'answer' => array_filter(array_slice($item, 2), 'strlen'),
            'ctime' => time()
          ];
        };
        $id = $this->questions->insert($data);

        unlink($file);//删除临时文件
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        return $this->respondWithData(['last_id' => $id]);
      } else {
        return $this->respondWithError('数据上传失败！');
      }
    } else {
      return $this->respondWithError('文件上传失败！');
    }
  }

  /**
   * @param string $directory
   * @param UploadedFileInterface $uploadedFile
   * @param string $filename
   * @return string
   * @throws Exception
   */
  private function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile, string $filename = ''): string
  {
    $extension = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));

    if ($filename == '') {
      // see http://php.net/manual/en/function.random-bytes.php
      $basename = bin2hex(random_bytes(8));
      $filename = sprintf('%s.%0.8s', $basename, $extension);
    }

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
  }
}