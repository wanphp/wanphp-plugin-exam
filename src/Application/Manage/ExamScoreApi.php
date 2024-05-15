<?php

namespace Wanphp\Plugins\Exam\Application\Manage;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Plugins\Exam\Application\Api;
use Wanphp\Plugins\Exam\Domain\ExamQuestionsInterface;
use Wanphp\Plugins\Exam\Domain\ExamScoreInterface;

class ExamScoreApi extends Api
{
  private ExamQuestionsInterface $questions;
  private ExamScoreInterface $examScore;

  public function __construct(ExamQuestionsInterface $questions, ExamScoreInterface $examScore)
  {
    $this->questions = $questions;
    $this->examScore = $examScore;
  }

  /**
   * @inheritDoc
   * @OA\Get(
   *  path="/admin/exam/score/{id}",
   *  tags={"Exam"},
   *  summary="查询成绩",
   *  operationId="GetScore",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="id",
   *    in="path",
   *    description="成绩ID",
   *    required=true,
   *    @OA\Schema(format="int64",type="integer")
   *  ),
   *  @OA\Response(
   *    response="200",
   *    description="请求成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(ref="#/components/schemas/ExamScoreEntity")
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'GET';
        $id = (int)$this->resolveArg('id');
        $score = $this->examScore->get('uid,questions[JSON],answer[JSON],startTime,endTime,score', ['id' => $id]);
        $questions = $this->questions->select('id,question,answerItem,orderly', ['id' => $score['questions']]);
        return $this->respondWithData(['questions' => $questions, 'score' => $score]);
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
