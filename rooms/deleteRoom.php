<?php

require "../_includes/bootstrap.inc.php";

final class DeleteRoomPage extends BaseCRUDPage
{

    private ?int $room_id;

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



        if($this->state === self::STATE_PROCESSED) {
            // je hotovo, reportujeme
            if ($this->result === self::RESULT_SUCCESS){
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='3;url=./roomList.php'>";
                $this->title = "Místnost smazána";
            } elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Smazání místnosti selhalo";
            }
        } elseif ($this->state === self::STATE_DELETE_REQUESTED) {
            // načíst data
            $this->room_id = $this->readPost();
            // validovat data
            if (!$this->room_id) {
                throw  new RequestException(400);
            }

            $token = bin2hex(random_bytes(20));

            if ($this->isHomeRoom($this->room_id)) {
                $this->sesionStorage->set($token, ['result' => self::RESULT_FAIL ]);
//                $this->redirect(self::RESULT_FAIL);
            } elseif (RoomModel::deleteById($this->room_id)) {
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
            return $this->m->render("roomSuccess", ["message" => "Smazání místnosti proběhlo úspěšně"]);
        } elseif ($this->result === self::RESULT_FAIL){
            return $this->m->render("roomFail", ["message" => "Smazání místnosti selhalo, zkontrolujte jestli v místnosti někdo nesídlí"]);
        }  elseif ($this->state === self::STATE_REJECTED){
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
        $room_id = filter_input( INPUT_POST, 'room_id', FILTER_VALIDATE_INT);

        return $room_id;
    }

    private function isHomeRoom(int $room_id) : bool {
        $stmt = $this->pdo->prepare("SELECT room FROM employee");

        $stmt->execute();

        while ($row = $stmt->fetch()){
            if($row->room === $room_id){
                return true;
            }
        }

        return false;
    }
}

$page = new DeleteRoomPage();
$page->render();

