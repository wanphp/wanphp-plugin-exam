<?php

namespace Wanphp\Plugins\Exam\Application;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Plugins\Exam\Domain\ExamInterface;
use Wanphp\Plugins\Exam\Domain\ExamQuestionsInterface;

class randQuestionsApi extends Api
{
  private ExamQuestionsInterface $questions;
  private ExamInterface $exam;

  public function __construct(ExamQuestionsInterface $questions, ExamInterface $exam)
  {
    $this->questions = $questions;
    $this->exam = $exam;
  }

  /**
   * @inheritDoc
   * @OA\Get(
   *  path="/api/exam/randquestion/{id}",
   *  tags={"Exam"},
   *  summary="获取指定科目考试试题",
   *  operationId="GetRandQuestion",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="id",
   *    in="path",
   *    description="考试科目ID",
   *    required=true,
   *    @OA\Schema(format="int64",type="integer")
   *  ),
   *  @OA\Response(response="200",description="请求成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    $id = (int)$this->resolveArg('id');
    // 随机取考题
    if ($id > 0) {
      $size = $this->exam->get('size', ['id' => $id]);
      if ($size) return $this->respondWithData($this->questions->randQuestions($id, $size));
      else return $this->respondWithError('考题数量错误');
    } else {
      return $this->respondWithError('ID错误');
    }
  }
}