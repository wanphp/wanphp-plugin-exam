<?php

namespace Wanphp\Plugins\Exam\Application\Manage;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Libray\Slim\WpUserInterface;
use Wanphp\Plugins\Exam\Domain\ExamInterface;
use Wanphp\Plugins\Exam\Domain\ExamScoreInterface;

class OutputExamRecordApi extends \Wanphp\Plugins\Exam\Application\Api
{
  private ExamInterface $exam;
  private ExamScoreInterface $examScore;
  private WpUserInterface $user;

  /**
   * @param ExamInterface $exam
   * @param ExamScoreInterface $examScore
   * @param WpUserInterface $user
   */
  public function __construct(
    ExamInterface      $exam,
    ExamScoreInterface $examScore,
    WpUserInterface    $user
  )
  {
    $this->exam = $exam;
    $this->examScore = $examScore;
    $this->user = $user;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $where = ['examId' => $this->resolveArg('id')];

    $res = $this->examScore->select('uid,score,startTime,endTime', $where);

    if ($res) {
      $title = $this->exam->get('title', ['id' => $this->resolveArg('id')]);

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $worksheet = $spreadsheet->getActiveSheet();
      $worksheet->setTitle('答题记录');

      $worksheet
        ->setCellValue('A1', '姓名')
        ->setCellValue('A1', '姓名')
        ->setCellValue('B1', '电话')
        ->setCellValue('C1', '单位')
        ->setCellValue('D1', '得分')
        ->setCellValue('E1', '用时(秒)')
        ->setCellValue('F1', '答题时间');

      $uid = array_column($res, 'uid');
      $users = [];
      foreach ($this->user->getUsers($uid) as $item) {
        $users[$item['id']] = ['name' => $item['name'], 'tel' => $item['tel'], 'department' => $item['remark']];
      }
      $i = 1;

      foreach ($res as $item) {
        $i++;
        $worksheet
          ->setCellValue('A' . $i, $users[$item['uid']]['name'])
          ->setCellValue('B' . $i, $users[$item['uid']]['tel'] . "\t")
          ->setCellValue('C' . $i, $users[$item['uid']]['department'])
          ->setCellValue('D' . $i, $item['score'])
          ->setCellValue('E' . $i, $item['endTime'] - $item['startTime'])
          ->setCellValue('F' . $i, date('Y-m-d H:i:s', $item['startTime']));
      }

      $this->response = $this->response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->withHeader('Content-Disposition', 'attachment;filename="' . $title . '参与记录.xlsx"')
        ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->withHeader('Pragma', 'no-cache');
      $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
      // 限制内存为 5 MB，超出后存入临时文件中。
      //$fiveMBs = 5 * 1024 * 1024;
      //$stream = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
      //一直把数据储存在内存中
      $stream = fopen('php://memory', 'r+');
      // 表格写入到内存
      $writer->save($stream);
      // 读取写入数据
      rewind($stream);

      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);
      $this->response->getBody()->write(stream_get_contents($stream));
      return $this->response;

    } else {
      return $this->respondWithError('暂无答题记录');
    }
  }
}
