<?php
declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Psr\Http\Server\MiddlewareInterface as Middleware;

return function (App $app, Middleware $PermissionMiddleware, Middleware $OAuthServerMiddleware) {
  $app->group('/api', function (Group $group) {
    //考试科目
    $group->get('/exam/{id:[0-9]+}', \Wanphp\Libray\Slim\ExamApi::class);
    //考试题目
    $group->get('/exam/question/{id:[0-9]+}', \Wanphp\Libray\Slim\ExamQuestionsApi::class);
    //考试成绩
    $group->map(['GET', 'POST'], '/exam/score[/{id:[0-9]+}]', \Wanphp\Libray\Slim\ExamScoreApi::class);
  })->addMiddleware($OAuthServerMiddleware);

  $app->group('/admin', function (Group $group) {
    //考试科目管理
    $group->map(['PUT', 'POST', 'DELETE'], '/exam[/{id:[0-9]+}]', \Wanphp\Libray\Slim\ExamApi::class);
    //考试题目管理
    $group->map(['PUT', 'POST', 'DELETE'], '/exam/question[/{id:[0-9]+}]', \Wanphp\Libray\Slim\ExamQuestionsApi::class);
  })->addMiddleware($PermissionMiddleware);
};


