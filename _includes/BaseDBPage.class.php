<?php


abstract class BaseDBPage extends BasePage
{
    protected ?PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = DB::getConection();
    }

    protected function isLogin() : bool {
        $login = new SessionStorage();
        if ($login->get('login')) return true;
        else return false;
    }

    protected function isAdmin() : bool {
        $login = new SessionStorage();
        if ($login->get('admin')) return true;
        else return false;
    }

    protected function whoIsLogin() : int {
        $login = new SessionStorage();
        return $login->get('employee_id');
    }

}