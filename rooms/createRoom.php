<?php

require "../_includes/bootstrap.inc.php";

final class CreateRoomPage extends BaseCRUDPage
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
                $this->title = "Místnost založena";
            } elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Založení místnosti selhalo";
            }
        } elseif ($this->state === self::STATE_FORM_SENT){
            // načíst data
            $this->room = $this->readPost();
            // validovat data
            if ($this->room->isValid()){

                $token = bin2hex(random_bytes(20));
                // uložit a přesměrovat
                if ($this->room->insert()){
                    $this->sesionStorage->set($token, ['result' => self::RESULT_SUCCESS ]);
                } else {
                    $this->sesionStorage->set($token, ['result' => self::RESULT_FAIL ]);
                }

                $this->redirect($token);

            } else {
                // jít na formulář
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Založit místnost: Neplatný formulář";
            }
        } elseif ($this->state !== self::STATE_REJECTED) {
            // přejít na formulář
            $this->title = "Založit místnost";
            $this->room = new RoomModel();
        }

    }

    protected function body(): string
    {
        if($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render("roomForm", ['create' => true, 'room' => $this->room ]);
        } elseif ($this->state === self::STATE_PROCESSED) {
            if ($this->result === self::RESULT_SUCCESS){
                return $this->m->render("roomSuccess", ["message" => "Vytvoření místnosti proběhlo úspěšně"]);
            } elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("roomFail", ["message" => "Vytvoření místnosti selhalo"]);
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

        if ($action === 'create'){
            return self::STATE_FORM_SENT;
        } else {
            return self::STATE_FORM_REQUESTED;
        }
    }

    private function readPost() : RoomModel {
        $room = [];
        $room['name'] = filter_input( INPUT_POST, 'name');
        $room['no'] = filter_input( INPUT_POST, 'no');
        $room['phone'] = filter_input( INPUT_POST, 'phone');

        if(!$room['phone'])
            $room['phone'] = null;

        return new RoomModel($room);
    }
}

$page = new CreateRoomPage();
$page->render();

