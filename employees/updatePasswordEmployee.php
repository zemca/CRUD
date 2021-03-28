<?php

require "../_includes/bootstrap.inc.php";

final class UpdateEmployeePasswordPage extends BaseCRUDPage
{

    private ?int $employee_id;
    private EmployeePasswordModel $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->state = $this->getSate();

        $this->employee_id = $this->findId();
        if(!$this->isLogin()) {
            $this->title = "Chyba";
            $this->state = self::STATE_REJECTED;
        }
//            header("Location: ../login/login.php");
        if(!$this->isAdmin()) {
            if($this->employee_id !== $this->whoIsLogin()) {

                $this->title = "Chyba";
                $this->state = self::STATE_REJECTED;
//                header("Location: ../login/login.php");
            }
        }


        if($this->state === self::STATE_PROCESSED) {
            // je hotovo, reportujeme
            if ($this->result === self::RESULT_SUCCESS){
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='3;url=./employeeList.php'>";
                $this->title = "Úprava hesla byla úspěšná";
            } elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Aktualizace hesla selhala";
            }
        } elseif ($this->state === self::STATE_FORM_SENT){
            // načíst data
            $this->employee = $this->readPost();
            // validovat data
            if ($this->employee->isValid()){
                // uložit a přesměrovat
                $token = bin2hex(random_bytes(20));

                if ($this->employee->update()){
                    $this->sesionStorage->set($token, ['result' => self::RESULT_SUCCESS ]);
                } else {
                    $this->sesionStorage->set($token, ['result' => self::RESULT_FAIL ]);
                }

                $this->redirect($token);

            } else {
                // jít na formulář
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Aktualizovat zaměstnance: Neplatný formulář";
            }
        } elseif ($this->state !== self::STATE_REJECTED) {
            // přejít na formulář
            $this->title = "Aktualizovat zaměstnance";
            $employee_id = $this->findId();
            if(!$employee_id)
                throw new RequestException(400);
            $this->employee = EmployeePasswordModel::getById($employee_id);
            if (!$this->employee)
                throw new RequestException(400);
        }

    }

    protected function body(): string
    {
        if($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render("employeePasswordForm", ['update' => true, 'employee' => $this->employee]);
        } elseif ($this->state === self::STATE_PROCESSED) {
            if ($this->result === self::RESULT_SUCCESS){
                return $this->m->render("employeeSuccess", ["message" => "Upravení hesla proběhlo úspěšně"]);
            } elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("employeeFail", ["message" => "Upravení hesla selhalo"]);
            }
        } elseif ($this->state === self::STATE_REJECTED){
            return $this->m->render("fail", ["message" => "Nejste přihlášen, nebo nemáte dostatečná práva"]);
        }

    }

    protected function getSate() : int {
        if($this->isProcessed()){
            return self::STATE_PROCESSED;
        }

        $action = filter_input( INPUT_POST, 'action');
        if ($action === 'update'){
            return self::STATE_FORM_SENT;
        } else {
            return self::STATE_FORM_REQUESTED;
        }
    }

    private function findId() : ?int {
        $employee_id = filter_input( INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);
        return $employee_id;
    }

    private function readPost() : EmployeePasswordModel {
//        var_dump($_POST);
        $employee = [];
        $employee['employee_id'] = $this->findId();
        $employee['password'] = password_hash(filter_input( INPUT_POST, 'password'), PASSWORD_BCRYPT);
//        dumpe($employee);
        return new EmployeePasswordModel($employee);
    }

}

$page = new UpdateEmployeePasswordPage();
$page->render();
