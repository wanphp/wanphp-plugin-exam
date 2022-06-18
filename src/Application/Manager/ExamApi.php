<?php

namespace Wanphp\Plugins\Exam\Application\Manager;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Plugins\Exam\Application\Api;
use Wanphp\Plugins\Exam\Domain\ExamInterface;

/**
 * ExamApi
 * @title 自定义菜单
 * @route /admin/exam
 * @package Wanphp\Plugins\Exam\Application\Manager
 */
class ExamApi extends Api
{
  private ExamInterface $exam;

  public function __construct(ExamInterface $exam)
  {
    $this->exam = $exam;
  }

  /**
   * @inheritDoc
   * @OA\Post(
   *  path="/admin/exam",
   *  tags={"Exam item"},
   *  security={{"bearerAuth":{}}},
   *  summary="添加考试科目",
   *  operationId="addExam",
   *   @OA\RequestBody(
   *     description="考试科目",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/ExamEntity")
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="创建成功",
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
   * @OA\Put(
   *  path="/admin/exam/{id}",
   *  tags={"Exam item"},
   *  security={{"bearerAuth":{}}},
   *  summary="修改考试科目",
   *  operationId="editExam",
   *   @OA\Parameter(
   *     name="id",
   *     in="path",
   *     description="科目ID",
   *     required=true,
   *     @OA\Schema(format="int64",type="integer")
   *   ),
   *   @OA\RequestBody(
   *     description="指定需要更新数据",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(ref="#/components/schemas/ExamEntity")
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="更新成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="upNum",type="integer")
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Delete(
   *  path="/admin/exam/{id}",
   *  tags={"Exam item"},
   *  summary="删除考试科目",
   *  operationId="delExam",
   *  security={{"bearerAuth":{}}},
   *  @OA\Parameter(
   *    name="id",
   *    in="path",
   *    description="科目ID",
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
   *  @OA\Get(
   *  path="/api/exam",
   *  tags={"Exam item"},
   *  summary="考试科目管理",
   *  operationId="ExamItemManager",
   *  security={{"bearerAuth":{}}},
   *  @OA\Response(response="200",description="请求成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'POST':
        $data = $this->getFormData();
        $id = $this->exam->get('id', ['title' => $data['title']]);
        if (is_numeric($id) && $id > 0) {
          return $this->respondWithError('考试科目已添加过');
        } else {
          $data['ctime'] = time();
          return $this->respondWithData(['id' => $this->exam->insert($data)], 201);
        }
      case 'PUT':
        $data = $this->getFormData();
        $id = (int)$this->resolveArg('id');
        if (isset($data['title'])) {
          $exam_id = $this->exam->get('id', ['id[!]' => $id, 'title' => $data['title']]);
          if ($exam_id) {
            return $this->respondWithError('考试科目已存在');
          }
        }
        if ($id > 0) {
          return $this->respondWithData(['upNum' => $this->exam->update($data, ['id' => $id])], 201);
        } else {
          return $this->respondWithError('ID错误');
        }
      case 'DELETE':
        $id = (int)$this->resolveArg('id');
        if ($id > 0) {
          return $this->respondWithData(['delNum' => $this->exam->delete(['id' => $id])]);
        } else {
          return $this->respondWithError('ID错误');
        }
      case 'GET';
        if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          $where = [];
          $params = $this->request->getQueryParams();
          if (!empty($params['search']['value'])) {
            $keyword = trim($params['search']['value']);
            $where['title[~]'] = $keyword;
          }

          $recordsFiltered = $this->exam->count('id', $where);
          $where['LIMIT'] = $this->getLimit();
          $where['ORDER'] = $this->getOrder();

          $data = [
            "draw" => $params['draw'],
            "recordsTotal" => $this->exam->count('id'),
            "recordsFiltered" => $recordsFiltered,
            'data' => $this->exam->select('*', $where)
          ];
          return $this->respondWithData($data);
        } else {
          $data = [
            'title' => '考试科目管理'
          ];

          return $this->respondView('@exam/exam-list.html', $data);
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}