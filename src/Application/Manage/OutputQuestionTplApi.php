<?php

namespace Wanphp\Plugins\Exam\Application\Manage;

use Psr\Http\Message\ResponseInterface as Response;

class OutputQuestionTplApi extends \Wanphp\Plugins\Exam\Application\Api
{

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $this->response = $this->response
      ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
      ->withHeader('Content-Disposition', 'attachment;filename="问题模板.xlsx"')
      ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
      ->withAddedHeader('Cache-Control', 'post-check=0, pre-check=0')
      ->withHeader('Pragma', 'no-cache');

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $worksheet = $spreadsheet->getActiveSheet();
    $worksheet->setTitle('选择题');

    $worksheet->getCell('A1')->setValue('问题题目');
    $worksheet->getCell('B1')->setValue('答案选项A');
    $worksheet->getCell('C1')->setValue('答案选项B');
    $worksheet->getCell('D1')->setValue('答案选项C');
    $worksheet->getCell('E1')->setValue('答案选项D');
    $worksheet->getCell('F1')->setValue('正确答案AC');

    $spreadsheet->createSheet();
    $spreadsheet->setActiveSheetIndex(1);
    $worksheet = $spreadsheet->getActiveSheet();
    $worksheet->setTitle('填空题');
    $worksheet->getCell('A1')->setValue('问题题目');
    $worksheet->getCell('B1')->setValue('答案是否有序');
    $worksheet->getCell('C1')->setValue('填空一');
    $worksheet->getCell('D1')->setValue('填空二');
    $worksheet->getCell('E1')->setValue('填空三');
    $worksheet->getCell('F1')->setValue('更多填空');

    //选择表1
    $spreadsheet->setActiveSheetIndex(0);
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
  }
}
