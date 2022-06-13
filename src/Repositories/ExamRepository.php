<?php

namespace Wanphp\Plugins\Exam\Repositories;

use Wanphp\Libray\Mysql\Database;
use Wanphp\Plugins\Exam\Entities\ExamEntity;

class ExamRepository extends \Wanphp\Libray\Mysql\BaseRepository implements \Wanphp\Plugins\Exam\Domain\ExamInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, ExamEntity::class);
  }
}