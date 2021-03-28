<?php

require "../_includes/bootstrap.inc.php";

final class DeleteEmployeePage extends BaseCRUDPage
{

    private ?int $employee_id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->state = $this->getSate();

        if(!$this->isLogin())
        {
            $this->title = "Chyba";
            $this->state = self::STATE_REJECTED;
        }
        elseif (!$this->isAdmin())
        {
            $this->title = "Chyba";
            $this->state = self::STATE_REJECTED;
        }
        elseif ($this->whoIsLogin() === $this->readPost())
        {
            $this->title = "Chyba";
            $this->state = self::STATE_REJECTED;
        }



        if($this->state === self::STATE_PROCESSED) {
            // je hotovo, reportujeme
            if ($this->result === self::RESULT_SUCCESS){
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='3;url=./employeeList.php'>";
                $this->title = "Zaměstnanec smazán";
            } elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Smazání Zaměstnance selhalo";
            }
        } elseif ($this->state === self::STATE_DELETE_REQUESTED) {
            // načíst data
            $this->employee_id = $this->readPost();
            // validovat data
            if (!$this->employee_id) {
                throw  new RequestException(400);
            }

            $token = bin2hex(random_bytes(20));

            if (EmployeeModel::deleteById($this->employee_id)) {
                $this->sesionStorage->set($token, ['result' => self::RESULT_SUCCESS ]);
//                $this->redirect(self::RESULT_SUCCESS);
            } else {
                $this->sesionStorage->set($token, ['result' => self::RESULT_FAIL ]);
//                $this->redirect(self::RESULT_FAIL);
            }
            $this->redirect($token);
        }
    }

    protected function body(): string
    {
        if ($this->result === self::RESULT_SUCCESS){
            return $this->m->render("employeeSuccess", ["message" => "Smazání zaměstnance proběhlo úspěšně"]);
        } elseif ($this->result === self::RESULT_FAIL){
            return $this->m->render("employeeFail", ["message" => "Smazání zaměstnance selhalo"]);
        } elseif ($this->state === self::STATE_REJECTED){
            return $this->m->render("fail", ["message" => "Nejste přihlášen, nebo nemáte dostatečná práva"]);
        }
    }

    protected function getSate() : int {
        if($this->isProcessed()){
            return self::STATE_PROCESSED;
        }

        return self::STATE_DELETE_REQUESTED;
    }

    private function readPost() : ?int {
        $employee_id = filter_input( INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);

        return $employee_id;
    }
}

$page = new DeleteEmployeePage();
$page->render();

