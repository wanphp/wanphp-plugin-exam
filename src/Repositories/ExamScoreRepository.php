<?php

namespace Wanphp\Plugins\Exam\Repositories;

use Wanphp\Libray\Mysql\Database;
use Wanphp\Plugins\Exam\Entities\ExamScoreEntity;

class ExamScoreRepository extends \Wanphp\Libray\Mysql\BaseRepository implements \Wanphp\Plugins\Exam\Domain\ExamScoreInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, ExamScoreEntity::class);
  }
}