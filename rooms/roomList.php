<?php
    require "../_includes/bootstrap.inc.php";

    final class ListRoomsPage extends BaseDBPage {

        private $state = true;

        protected function setUp(): void
        {
            parent::setUp();
            if(!$this->isLogin())
            {
                $this->title = "Chyba";
                $this->state = false;
            }
            $this->title = "Seznam místností";
        }

        protected function body(): string
        {
            if (!$this->state)
                return $this->m->render("fail", ["message" => "Nejste přihlášen, nebo nemáte dostatečná práva"]);
            else {
                $stmt = $this->pdo->prepare("SELECT * FROM `room` ORDER BY `name`");
                $stmt->execute([]);
                return $this->m->render("roomList", ["roomDetail" => "room.php", "rooms" => $stmt]);
            }
        }
    }

    $page = new ListRoomsPage();
    $page->render();

