<?php

namespace Wanphp\Plugins\Exam\Entities;

use Wanphp\Libray\Mysql\EntityTrait;

/**
 * @OA\Schema(
 *   title="题库",
 *   description="题库",
 *   required={"question","answer"}
 * )
 */
class ExamQuestionsEntity implements \JsonSerializable
{
  use EntityTrait;

  /**
   * @DBType({"key":"PRI","type":"smallint NOT NULL AUTO_INCREMENT"})
   * @var integer|null
   */
  private ?int $id;
  /**
   * @DBType({"key": "MUL","type":"smallint(11) NULL DEFAULT NULL"})
   * @OA\Property(description="知识竞赛ID")
   * @var integer
   */
  private int $examId;
  /**
   * @DBType({"type":"varchar(300) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="题目内容")
   */
  private string $question;
  /**
   * @DBType({"type":"json NULL"})
   * @var array
   * @OA\Property(@OA\Items(),description="选择题答案选项,填空题留空")
   */
  private array $answerItem;
  /**
   * @DBType({"type":"json NULL"})
   * @var array
   * @OA\Property(@OA\Items(),description="正确答案")
   */
  private array $answer;
  /**
   * @DBType({"type":"tinyint(1) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="填空题答案是否有序")
   */
  private int $orderly;
  /**
   * @DBType({"type":"char(10) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="入库时间")
   * @var integer
   */
  private int $ctime;
}
