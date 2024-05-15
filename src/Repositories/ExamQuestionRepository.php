<?php

namespace Wanphp\Plugins\Exam\Repositories;

use Wanphp\Libray\Mysql\Database;
use Wanphp\Plugins\Exam\Entities\ExamQuestionsEntity;

class ExamQuestionRepository extends \Wanphp\Libray\Mysql\BaseRepository implements \Wanphp\Plugins\Exam\Domain\ExamQuestionsInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, ExamQuestionsEntity::class);
  }

  public function randQuestions(int $id, int $size): array
  {
    return $this->db->rand($this->tableName, ['id', 'question', 'answerItem[JSON]', 'answer[JSON]', 'orderly'], ['examId' => $id, 'LIMIT' => $size]);
  }
}