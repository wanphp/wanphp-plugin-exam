<?php

namespace Wanphp\Plugins\Exam\Application;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Plugins\Exam\Domain\ExamQuestionsInterface;
use Wanphp\Plugins\Exam\Domain\ExamScoreInterface;

class UserScoreApi extends Api
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
   * @OA\Post(
   *  path="/api/user/score",
   *  tags={"Exam"},
   *  security={{"bearerAuth":{}}},
   *  summary="用户考试交卷",
   *  operationId="addScore",
   *   @OA\RequestBody(
   *     description="考试成绩",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/ExamScoreEntity")
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="提交成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="id",type="integer")
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Get(
   *  path="/api/user/score/{id}",
   *  tags={"Exam"},
   *  summary="查询考试成绩",
   *  operationId="userGetScore",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="id",
   *    in="path",
   *    description="考试成绩ID",
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
      case 'POST':
        $data = $this->getFormData();
        $data['uid'] = $this->getUid();
        $data['ctime'] = time();
        return $this->respondWithData(['id' => $this->examScore->insert($data)], 201);
      case 'GET';
        $where = [
          'id' => (int)$this->resolveArg('id'),
          'uid' => $this->getUid()
        ];
        $score = $this->examScore->get('uid,questions[JSON],answer[JSON],startTime,endTime,score', $where);
        $questions = $this->questions->select('id,question,answerItem,orderly', ['id' => $score['questions']]);
        return $this->respondWithData(['questions' => $questions, 'score' => $score]);
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}