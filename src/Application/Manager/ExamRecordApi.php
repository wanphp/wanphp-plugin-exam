<?php

namespace Wanphp\Plugins\Exam\Application\Manager;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Libray\User\User;
use Wanphp\Plugins\Exam\Domain\ExamInterface;
use Wanphp\Plugins\Exam\Domain\ExamQuestionsInterface;
use Wanphp\Plugins\Exam\Domain\ExamScoreInterface;

class ExamRecordApi extends \Wanphp\Plugins\Exam\Application\Api
{

  private ExamInterface $exam;
  private ExamQuestionsInterface $questions;
  private ExamScoreInterface $examScore;
  private User $user;

  /**
   * @param ContainerInterface $container
   * @param ExamInterface $exam
   * @param ExamQuestionsInterface $questions
   * @param ExamScoreInterface $examScore
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function __construct(
    ContainerInterface     $container,
    ExamInterface          $exam,
    ExamQuestionsInterface $questions,
    ExamScoreInterface     $examScore
  )
  {
    $this->exam = $exam;
    $this->questions = $questions;
    $this->examScore = $examScore;
    $user = $container->get('userServer');
    $redisConfig = $container->get('redis');
    $this->user = new User($user['appId'], $user['appSecret'], $user['apiUri'], $redisConfig);
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'POST':
        $id = (int)$this->resolveArg('id');
        $result = $this->examScore->get('question[JSON],answer[JSON]', ['id' => $id]);
        $question = $this->questions->select('id,question,answerItem[JSON],answer[JSON]', ['id' => $result['question']]);
        $questions = [];
        foreach ($question as $item) {
          $questions[$item['id']] = $question;
        }
        foreach ($result['question'] as &$id) {
          $id = $questions[$id];
        }
        return $this->respondWithData($result);
      case 'GET';
        if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          $params = $this->request->getQueryParams();
          $where = ['examId' => $this->resolveArg('id')];
          if (!empty($params['search']['value'])) {
            $keyword = trim($params['search']['value']);
            $where['OR'] = [
              'name[~]' => $keyword,
              'tel[~]' => $keyword
            ];
          }

          $recordsFiltered = $this->examScore->count('id', $where);
          $where['LIMIT'] = $this->getLimit();
          $where['ORDER'] = $this->getOrder();

          $res = $this->examScore->select('*', $where);
          if ($res) {
            $uid = array_column($res, 'uid');
            $users = [];
            foreach ($this->user->getUsers($uid) as $item) {
              $users[$item['id']] = ['nickname' => $item['nickname'], 'headimgurl' => $item['headimgurl']];
            }
            foreach ($res as &$item) $item['user'] = $users[$item['uid']];
          }
          $data = [
            "draw" => $params['draw'],
            "recordsTotal" => $this->examScore->count('id', ['examId' => $params['id']]),
            "recordsFiltered" => $recordsFiltered,
            'data' => $res
          ];
          return $this->respondWithData($data);
        } else {
          $id = (int)$this->resolveArg('id');
          $data = [
            'title' => $this->exam->get('title', ['id' => $id]) . '考试记录',
            'id' => $id
          ];

          return $this->respondView('@exam/exam-record.html', $data);
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}