<?php

namespace Wanphp\Plugins\Exam\Application;

use Wanphp\Libray\Slim\Action;

/**
 * @OA\Info(
 *     description="在线知识竞赛插件，插件不能单独运行",
 *     version="1.0.0",
 *     title="在线知识竞赛"
 * )
 * @OA\Tag(
 *     name="Exam",
 *     description="前端知识竞赛"
 * )
 * @OA\Tag(
 *     name="Exam item",
 *     description="知识竞赛管理"
 * )
 * @OA\Tag(
 *     name="Question bank",
 *     description="后端题库管理"
 * )
 */

/**
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 * )
 * @OA\Schema(
 *   title="出错提示",
 *   schema="Error",
 *   type="object"
 * )
 * @OA\Schema(
 *   title="成功提示",
 *   schema="Success",
 *   type="object"
 * )
 */
abstract class Api extends Action
{
}
