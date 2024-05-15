<?php

namespace Wanphp\Plugins\Exam\Application;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Wanphp\Libray\User\User;
use Wanphp\Plugins\Exam\Domain\ExamInterface;
use Wanphp\Plugins\Exam\Domain\ExamQuestionsInterface;
use Wanphp\Plugins\Exam\Domain\ExamScoreInterface;

class UserScoreApi extends Api
{
  private ExamInterface $exam;
  private ExamQuestionsInterface $questions;
  private ExamScoreInterface $examScore;
  private User $user;

  public function __construct(
    ExamInterface          $exam,
    ExamQuestionsInterface $questions,
    ExamScoreInterface     $examScore,
    User                   $user)
  {
    $this->exam = $exam;
    $this->questions = $questions;
    $this->examScore = $examScore;
    $this->user = $user;
  }

  /**
   * @inheritDoc
   * @OA\Post(
   *  path="/api/user/score",
   *  tags={"Exam"},
   *  security={{"bearerAuth":{}}},
   *  summary="用户交卷",
   *  operationId="addScore",
   *   @OA\RequestBody(
   *     description="成绩",
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
   *  summary="查询成绩",
   *  operationId="userGetScore",
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
      case 'POST':
        $data = $this->getFormData();
        $data['uid'] = $this->getUid();
        $user = $this->user->getUser($data['uid']);
        if ($user['name']) $data['name'] = $user['name'];
        if ($user['tel']) $data['tel'] = $user['tel'];
        $data['endTime'] = time();
        // 参与时间
        $examTime = $this->exam->get('examTime', ['id' => $data['examId']]);
        if ($examTime) $examTime *= 60;
        if ($examTime && ($data['endTime'] - $data['startTime']) <= $examTime && ($data['endTime'] - $data['startTime']) > 0) {
          $id = $this->examScore->get('id', ['uid' => $data['uid'], 'examId' => $data['examId']]);
          if ($id) return $this->respondWithData(['id' => $id], 201);
          else return $this->respondWithData(['id' => $this->examScore->insert($data)], 201);
        } else {
          return $this->respondWithError('答题超时！交卷失败。');
        }
      case 'GET';
        $examId = $this->args['id'] ?? 0;
        if ($examId > 0) {
          $where = [
            'examId' => (int)$this->resolveArg('id'),
            'uid' => $this->getUid()
          ];
          $score = $this->examScore->get('id,questions[JSON],answer[JSON],startTime,endTime,score', $where);
          if ($score) {
            $questions = $this->questions->select('id,question,answerItem[JSON],answer[JSON],orderly', ['id' => $score['questions']]);
            // 按答题顺序排序
            $sort = array_column($questions, 'id');
            $res = [];
            foreach ($score['questions'] as $id) $res[] = $questions[array_search($id, $sort)];
            return $this->respondWithData(['questions' => $res, 'score' => $score]);
          } else {
            return $this->respondWithData();
          }
        } else {
          $examId = $this->examScore->get('examId', ['uid' => $this->getUid()]);
          if ($examId) {
            $exam = $this->exam->select('id,title', ['id' => $examId]);
            $arr = array_column($exam, 'title', 'id');

            $users = $this->examScore->select('id,examId,score,startTime,endTime', ['uid' => $this->getUid(), 'examId' => $examId]);
            if ($users) {
              foreach ($users as &$u) {
                $u['exam'] = $arr[$u['examId']];
                // 答题用时
                $u['examTime'] = $u['endTime'] - $u['startTime'];
              }
              return $this->respondWithData($users);
            } else return $this->respondWithData();
          } else return $this->respondWithData();
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }

  public function currentServerTime(Request $request, Response $response, array $args): Response
  {
    $this->request = $request;
    $this->response = $response;
    $this->args = $args;
    return $this->respondWithData(['time' => time()]);
  }
}
