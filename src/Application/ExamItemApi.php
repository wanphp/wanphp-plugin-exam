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
    $id = (int)$this->resolveArg('id');
    if ($id > 0) {
      return $this->respondWithData($this->exam->get('*', ['id' => $id]));
    } else {
      return $this->respondWithError('ID错误');
    }
  }
}