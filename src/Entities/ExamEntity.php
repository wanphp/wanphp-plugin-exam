<?php

namespace Wanphp\Plugins\Exam\Entities;

use Wanphp\Libray\Mysql\EntityTrait;

/**
 * @OA\Schema(
 *   title="考题科目",
 *   description="考题科目，考题内容",
 *   required={"title"}
 * )
 */
class ExamEntity implements \JsonSerializable
{

  use EntityTrait;

  /**
   * @DBType({"key":"PRI","type":"smallint NOT NULL AUTO_INCREMENT"})
   * @var integer|null
   */
  private ?int $id;
  /**
   * @DBType({"type":"varchar(100) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="考题科目")
   */
  private string $title;
  /**
   * @DBType({"type":"varchar(500) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="考题科目介绍")
   */
  private string $description;
  /**
   * @DBType({"type":"text NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="考题科目说明")
   */
  private string $content;
  /**
   * @DBType({"type":"varchar(50) NOT NULL DEFAULT ''"})
   * @var string
   * @OA\Property(description="封面、海报")
   */
  private string $cover;
  /**
   * @DBType({"type":"char(3) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="每次抽取考题数量,最多999题，成绩按满分100分计算")
   */
  private int $size;
  /**
   * @DBType({"type":"char(3) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="考试时间,最多999分钟")
   */
  private int $examTime;
  /**
   * @DBType({"type":"char(10) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="考题科目开始时间")
   */
  private int $startTime;
  /**
   * @DBType({"type":"char(10) NOT NULL DEFAULT 0"})
   * @var integer
   * @OA\Property(description="考题科目结束时间")
   */
  private int $endTime;
  /**
   * @DBType({"type":"char(10) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="创建时间")
   * @var integer
   */
  private int $ctime;
}