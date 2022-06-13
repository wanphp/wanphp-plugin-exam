<?php

namespace Wanphp\Plugins\Exam\Domain;

interface ExamQuestionsInterface extends \Wanphp\Libray\Mysql\BaseInterface
{
  const TABLE_NAME = "examQuestions";

  public function randQuestions(int $id,int $size): array;
}