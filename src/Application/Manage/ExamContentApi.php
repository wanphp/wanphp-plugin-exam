<?php

namespace Wanphp\Plugins\Exam\Application\Manage;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Plugins\Exam\Domain\ExamInterface;

class ExamContentApi extends \Wanphp\Plugins\Exam\Application\Api
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
      'title' => '添加/修改知识竞赛',
      'exam' => $exam ?? [],
      'time' => time()
    ];

    return $this->respondView('@exam/content.html', $data);
  }
}
