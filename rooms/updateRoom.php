<?php

require "../_includes/bootstrap.inc.php";

final class UpdateRoomPage extends BaseCRUDPage
{

    private RoomModel $room;

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
                $this->title = "Místnost upravena";
            } elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Aktualizace místnosti selhala";
            }
        } elseif ($this->state === self::STATE_FORM_SENT){
            // načíst data
            $this->room = $this->readPost();
            // validovat data
            if ($this->room->isValid()){
                // uložit a přesměrovat
                $token = bin2hex(random_bytes(20));

                if ($this->room->update()){
                    $this->sesionStorage->set($token, ['result' => self::RESULT_SUCCESS ]);
                } else {
                    $this->sesionStorage->set($token, ['result' => self::RESULT_FAIL ]);
                }

                $this->redirect($token);

            } else {
                // jít na formulář
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Aktualizovat místnost: Neplatný formulář";
            }
        } elseif ($this->state !== self::STATE_REJECTED) {
            // přejít na formulář
            $this->title = "Aktualizovat místnost";
            $room_id = $this->findId();
            if(!$room_id)
                throw new RequestException(400);
            $this->room = RoomModel::getById($room_id);
            if (!$this->room)
                throw new RequestException(400);
        }

    }

    protected function body(): string
    {
        if($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render("roomForm", ['update' => true, 'room' => $this->room ]);
        } elseif ($this->state === self::STATE_PROCESSED) {
            if ($this->result === self::RESULT_SUCCESS){
                return $this->m->render("roomSuccess", ["message" => "Upravení místnosti proběhlo úspěšně"]);
            } elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("roomFail", ["message" => "Upravení místnosti selhalo"]);
            }
        }  elseif ($this->state === self::STATE_REJECTED){
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
        $room_id = filter_input( INPUT_GET, 'room_id', FILTER_VALIDATE_INT);
        return $room_id;
    }

    private function readPost() : RoomModel {
        $room = [];
        $room['room_id'] = filter_input( INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        $room['name'] = filter_input( INPUT_POST, 'name');
        $room['no'] = filter_input( INPUT_POST, 'no');
        $room['phone'] = filter_input( INPUT_POST, 'phone');

        if(!$room['phone'])
            $room['phone'] = null;

        return new RoomModel($room);
    }
}

$page = new UpdateRoomPage();
$page->render();

