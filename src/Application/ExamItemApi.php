<?php

namespace Wanphp\Plugins\Exam\Application;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Plugins\Exam\Domain\ExamInterface;

class ExamItemApi extends Api
{
  private ExamInterface $exam;

  public function __construct(ExamInterface $exam)
  {
    $this->exam = $exam;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $id = $this->args['id'] ?? 0;
    if ($id > 0) {
      $exam = $this->exam->get('*', ['id' => $id]);
      if($exam){
        $exam['cover'] = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . $exam['cover'];
        return $this->respondWithData($exam);
      }else{
        return $this->respondWithError('未找到');
      }
    } else {
      $res = [];
      foreach ($this->exam->select('id,title,cover,description,startTime,endTime') as $data) {
        if ($data['cover']) $data['cover'] = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . $data['cover'];
        $res[] = $data;
      }

      return $this->respondWithData($res);
    }
  }
}