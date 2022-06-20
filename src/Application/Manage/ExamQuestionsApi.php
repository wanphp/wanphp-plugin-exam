<?php

namespace Wanphp\Plugins\Exam\Application\Manage;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Plugins\Exam\Application\Api;
use Wanphp\Plugins\Exam\Domain\ExamQuestionsInterface;

class ExamQuestionsApi extends Api
{

  private ExamQuestionsInterface $questions;

  public function __construct(ExamQuestionsInterface $questions)
  {
    $this->questions = $questions;
  }

  /**
   * @inheritDoc
   * @OA\Post(
   *  path="/admin/exam/question",
   *  tags={"Question bank"},
   *  security={{"bearerAuth":{}}},
   *  summary="添加考试试题",
   *  operationId="addQuestion",
   *   @OA\RequestBody(
   *     description="考试试题",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/ExamQuestionsEntity")
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="创建成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(ref="#/components/schemas/ExamQuestionsEntity")
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Put(
   *  path="/admin/exam/question/{id}",
   *  tags={"Question bank"},
   *  security={{"bearerAuth":{}}},
   *  summary="修改考试试题",
   *  operationId="editQuestion",
   *   @OA\Parameter(
   *     name="id",
   *     in="path",
   *     description="试题ID",
   *     required=true,
   *     @OA\Schema(format="int64",type="integer")
   *   ),
   *   @OA\RequestBody(
   *     description="指定需要更新数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/ExamQuestionsEntity")
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="更新成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(ref="#/components/schemas/ExamQuestionsEntity")
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Delete(
   *  path="/admin/exam/question/{id}",
   *  tags={"Question bank"},
   *  summary="删除考试试题",
   *  operationId="delQuestion",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="id",
   *    in="path",
   *    description="试题ID",
   *    required=true,
   *    @OA\Schema(format="int64",type="integer")
   *  ),
   *  @OA\Response(
   *    response="200",
   *    description="请求成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="delNum",type="integer")
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Get(
   *  path="/admin/exam/question/{id}",
   *  tags={"Question bank"},
   *  summary="考试试题管理",
   *  operationId="ListQuestion",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="id",
   *    in="path",
   *    description="科目ID",
   *    required=true,
   *    @OA\Schema(format="int64",type="integer")
   *  ),
   *  @OA\Response(response="200",description="请求成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'POST':
        $data = $this->getFormData();
        $id = $this->questions->get('id', ['question' => $data['question']]);
        if (is_numeric($id) && $id > 0) {
          return $this->respondWithError('考题已添加过');
        } else {
          $data['ctime'] = time();
          $data['id'] = $this->questions->insert($data);
          return $this->respondWithData($data, 201);
        }
      case 'PUT':
        $data = $this->getFormData();
        $id = (int)$this->resolveArg('id');
        if (isset($data['question'])) {
          $exam_id = $this->questions->get('id', ['id[!]' => $id, 'question' => $data['question']]);
          if ($exam_id) {
            return $this->respondWithError('考题已存在');
          }
        }
        if ($id > 0) {
          $this->questions->update($data, ['id' => $id]);
          $data['id'] = $id;
          return $this->respondWithData($data, 201);
        } else {
          return $this->respondWithError('ID错误');
        }
      case 'DELETE':
        $id = (int)$this->resolveArg('id');
        if ($id > 0) {
          return $this->respondWithData(['delNum' => $this->questions->delete(['id' => $id])]);
        } else {
          return $this->respondWithError('ID错误');
        }
      case 'GET':
        if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          $where = ['examId' => $this->resolveArg('id')];
          $params = $this->request->getQueryParams();
          if (!empty($params['search']['value'])) {
            $keyword = trim($params['search']['value']);
            $where['question[~]'] = $keyword;
          }

          $recordsFiltered = $this->questions->count('id', $where);
          $where['LIMIT'] = $this->getLimit();
          $where['ORDER'] = $this->getOrder();

          $data = [
            "draw" => $params['draw'],
            "recordsTotal" => $this->questions->count('id', ['examId' => $this->resolveArg('id')]),
            "recordsFiltered" => $recordsFiltered,
            'data' => $this->questions->select('*', $where)
          ];
          return $this->respondWithData($data);
        } else {
          $data = [
            'title' => '考试试题管理',
            'id' => $this->resolveArg('id')
          ];

          return $this->respondView('@exam/exam-question.html', $data);
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}