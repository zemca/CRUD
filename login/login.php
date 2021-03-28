<?php

require "../_includes/bootstrap.inc.php";

final class Login extends BaseCRUDPage
{

    private LoginModel $user;

    protected function setUp(): void
    {

        parent::setUp();

        $this->user = $this->readPost();

        if(!$this->user){
            $this->title = "Přihlášení uživatele";
            $this->state = self::STATE_FORM_REQUESTED;
        } else {
            if ($this->user->isValid()) {

                $token = bin2hex(random_bytes(20));

                if ($this->login_login()) {
                    $this->state = self::STATE_PROCESSED;
                    $this->sesionStorage->set($token, ['result' => self::RESULT_SUCCESS]);
                    $this->sesionStorage->set('login', true);
                    header("Location: ../rooms/roomList.php");
                } else {
                    $this->sesionStorage->set($token, ['result' => self::RESULT_FAIL]);
                    $this->user = new LoginModel();
                }

                $this->redirect($token);

            } else {
                $this->title = "Přihlášení uživatele";
                $this->user = new LoginModel();
                $this->state = self::STATE_FORM_REQUESTED;
            }
        }

    }

    protected function body(): string
    {
        if ($this->isLogin()) {
            return $this->m->render("isLogin", ["message" => "Přihlášení proběhlo úspěšně"]);
        } if ($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render("login");
        }

    }
    protected function getSate() : int{

    }
    private function readPost() : LoginModel {
        $user = [];
        $user['username'] = filter_input( INPUT_POST, 'username') ? filter_input( INPUT_POST, 'username') : "";
        $user['password'] = filter_input( INPUT_POST, 'password') ? filter_input( INPUT_POST, 'password') : "";

        return new LoginModel($user);
    }

    private function login_login() : bool {

        $stmt = $this->pdo->prepare("SELECT username, password, admin, employee_id FROM employee");

        $stmt->execute();

        while ($row = $stmt->fetch($this->pdo::FETCH_ASSOC)){
            if($row['username'] === $this->user->username){
                if(password_verify($this->user->password, $row['password'])){
                    if ($row['admin']){
                        $this->sesionStorage->set('admin', true);
                    } else {
                        $this->sesionStorage->set('admin', false);
                    }
                    $this->sesionStorage->set('employee_id', $row['employee_id']);
                    return true;
                }
            }
        }

        return false;
    }


}

$page = new Login();
$page->render();