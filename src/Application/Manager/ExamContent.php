<?php

namespace Wanphp\Plugins\Exam\Application\Manager;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Plugins\Exam\Domain\ExamInterface;

class ExamContent extends \Wanphp\Plugins\Exam\Application\Api
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
    $id = intval($this->args['id'] ?? 0);
    if ($id > 0) $exam = $this->exam->get('*', ['id' => $id]);
    $data = [
      'title' => '添加/修改考试科目',
      'exam' => $exam ?? [],
      'time' => time()
    ];

    return $this->respondView('@exam/content.html', $data);
  }
}