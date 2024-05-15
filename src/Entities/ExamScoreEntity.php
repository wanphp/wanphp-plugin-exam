<?php

namespace Wanphp\Plugins\Exam\Entities;

use JsonSerializable;
use Wanphp\Libray\Mysql\EntityTrait;

/**
 * @OA\Schema(
 *   title="答题记录",
 *   description="用户答题记录",
 *   required={"uid","questions"}
 * )
 */
class ExamScoreEntity implements JsonSerializable
{
  use EntityTrait;

  /**
   * @DBType({"key":"PRI","type":"int NOT NULL AUTO_INCREMENT"})
   * @var integer|null
   */
  private ?int $id;
  /**
   * @DBType({"key": "MUL","type":"smallint NULL DEFAULT NULL"})
   * @OA\Property(description="知识竞赛ID")
   * @var integer
   */
  private int $examId;
  /**
   * @DBType({"key": "MUL","type":"int(11) NULL DEFAULT NULL"})
   * @OA\Property(description="答题用户ID")
   * @var integer
   */
  private int $uid;
  /**
   * @DBType({"type":"varchar(20) NOT NULL DEFAULT ''"})
   * @OA\Property(description="姓名")
   * @var string
   */
  private string $name;
  /**
   * @DBType({"key": "MUL","type":"varchar(20) NULL DEFAULT NULL"})
   * @OA\Property(description="电话")
   * @var string
   */
  private string $tel;
  /**
   * @DBType({"type":"json NULL"})
   * @var array
   * @OA\Property(@OA\Items(),description="问题")
   */
  private array $questions;
  /**
   * @DBType({"type":"json NULL"})
   * @var array
   * @OA\Property(@OA\Items(),description="答案")
   */
  private array $answer;
  /**
   * @DBType({"type":"char(10) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="开始答题时间")
   */
  private int $startTime;
  /**
   * @DBType({"type":"char(10) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="结束答题时间")
   */
  private int $endTime;
  /**
   * @DBType({"type":"decimal(15,2) NOT NULL DEFAULT '0'"})
   * @var float
   * @OA\Property(description="得分")
   */
  private float $score;
}
