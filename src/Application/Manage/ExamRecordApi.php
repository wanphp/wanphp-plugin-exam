<?php

namespace Wanphp\Plugins\Exam\Application\Manage;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Libray\Slim\WpUserInterface;
use Wanphp\Plugins\Exam\Domain\ExamInterface;
use Wanphp\Plugins\Exam\Domain\ExamQuestionsInterface;
use Wanphp\Plugins\Exam\Domain\ExamScoreInterface;

class ExamRecordApi extends \Wanphp\Plugins\Exam\Application\Api
{

  private ExamInterface $exam;
  private ExamQuestionsInterface $questions;
  private ExamScoreInterface $examScore;
  private WpUserInterface $user;

  /**
   * @param ExamInterface $exam
   * @param ExamQuestionsInterface $questions
   * @param ExamScoreInterface $examScore
   * @param WpUserInterface $user
   */
  public function __construct(
    ExamInterface          $exam,
    ExamQuestionsInterface $questions,
    ExamScoreInterface     $examScore,
    WpUserInterface        $user
  )
  {
    $this->exam = $exam;
    $this->questions = $questions;
    $this->examScore = $examScore;
    $this->user = $user;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'POST':
        $id = (int)$this->resolveArg('id');
        $result = $this->examScore->get('questions[JSON],answer[JSON]', ['id' => $id]);
        $question = $this->questions->select('id,question,answerItem[JSON],answer[JSON]', ['id' => $result['questions']]);
        $questions = [];
        foreach ($question as $item) {
          $questions[$item['id']] = $item;
        }
        foreach ($result['questions'] as &$id) {
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
            "recordsTotal" => $this->examScore->count('id', ['examId' => $this->resolveArg('id')]),
            "recordsFiltered" => $recordsFiltered,
            'data' => $res
          ];
          return $this->respondWithData($data);
        } else {
          $id = (int)$this->resolveArg('id');
          $data = [
            'title' => $this->exam->get('title', ['id' => $id]) . '参与记录',
            'id' => $id
          ];

          return $this->respondView('@exam/exam-record.html', $data);
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
