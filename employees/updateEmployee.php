<?php

require "../_includes/bootstrap.inc.php";

final class UpdateEmployeePage extends BaseCRUDPage
{

    private array $rooms;
    private EmployeeModel $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->state = $this->getSate();

        if(!$this->isLogin())
        {
            $this->title = "Chyba";
            $this->state = self::STATE_REJECTED;
        }
        if(!$this->isAdmin())
        {
            $this->title = "Chyba";
            $this->state = self::STATE_REJECTED;
        }


        $this->rooms = $this->getRooms();

        if($this->state === self::STATE_PROCESSED) {
            // je hotovo, reportujeme
            if ($this->result === self::RESULT_SUCCESS){
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='3;url=./employeeList.php'>";
                $this->title = "Zaměstnanec upraven";
            } elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Aktualizace zaměstnance selhala";
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
            $this->employee = EmployeeModel::getById($employee_id);
            if (!$this->employee)
                throw new RequestException(400);
        }

    }

    protected function body(): string
    {
        if($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render("employeeForm", ['update' => true, 'employee' => $this->employee, 'rooms' => $this->rooms ]);
        } elseif ($this->state === self::STATE_PROCESSED) {
            if ($this->result === self::RESULT_SUCCESS){
                return $this->m->render("employeeSuccess", ["message" => "Upravení zaměstnance proběhlo úspěšně"]);
            } elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("employeeFail", ["message" => "Upravení zaměstnance selhalo"]);
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

    private function readPost() : EmployeeModel {
        $employee = [];
        $employee['employee_id'] = filter_input( INPUT_POST, 'employee_id');
        $employee['name'] = filter_input( INPUT_POST, 'name');
        $employee['surname'] = filter_input( INPUT_POST, 'surname');
        $employee['job'] = filter_input( INPUT_POST, 'job');
        $employee['wage'] = filter_input( INPUT_POST, 'wage');
        $employee['room'] = filter_input( INPUT_POST, 'room');
        $employee['username'] = filter_input( INPUT_POST, 'username');
        $employee['password'] = password_hash(filter_input( INPUT_POST, 'password'), PASSWORD_BCRYPT);
        $employee['admin'] = filter_input( INPUT_POST, 'admin', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return new EmployeeModel($employee);
    }
    private function getRooms() : array {
        $rooms = [];
        $stmt = $this->pdo->prepare("SELECT name, room_id FROM room");

        $stmt->execute();

        while ($row = $stmt->fetch()){
            $rooms[] = $row;
        }

        return $rooms;
    }
}

$page = new UpdateEmployeePage();
$page->render();
