<?php


class RoomModelBack
{
    const TABLE = "room";
    private ?int $room_id = null;

    public function getRoomId(): ?int
    {
        return $this->room_id;
    }

    public string $name = "";
    public string $no = "";
    public ?string $phone = null;

    public function __construct(array $rawData = []) {
        if (array_key_exists('room_id', $rawData)) {
            $this->room_id = $rawData['room_id'];
        }
        if (array_key_exists('name', $rawData)) {
            $this->name = $rawData['name'];
        }
        if (array_key_exists('no', $rawData)) {
            $this->no = $rawData['no'];
        }
        if (array_key_exists('phone', $rawData)) {
            $this->phone = $rawData['phone'];
        }
    }

    public function insert() : bool {
        $query = "INSERT INTO ".self::TABLE." (name, no, phone) VALUE (:name, :no, :phone)";

        $stmt = DB::getConection()->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':no', $this->no);
        $stmt->bindParam(':phone', $this->phone);

        if (!$stmt->execute()){
            return false;
        }

        $this->room_id = DB::getConection()->lastInsertId();

        return true;
    }

    public function update() : bool {
        $query = "UPDATE ".self::TABLE." SET name = :name, phone = :phone, no = :no WHERE room_id = :room_id";


        $stmt = DB::getConection()->prepare($query);
        $stmt->bindParam(':room_id', $this->room_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':no', $this->no);
        $stmt->bindParam(':phone', $this->phone);

        return $stmt->execute();
    }

    public function delete() : bool {
        self::deleteById($this->room_id);
    }

    public static function deleteById(int $room_id) : bool {
        $query = "DELETE FROM ".self::TABLE." WHERE room_id = :room_id";

        $stmt = DB::getConection()->prepare($query);
        $stmt->bindParam('room_id', $room_id);

        return $stmt->execute();
    }

    public static function getById(int $room_id) : ?self {
        $query = "SELECT room_id, name, no, phone FROM ".self::TABLE." WHERE room_id = :room_id";

        $stmt = DB::getConection()->prepare($query);
        $stmt->bindParam(':room_id', $room_id);

        if (!$stmt->execute())
            return null;

        $roomData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$roomData)
            return null;

        return new self($roomData);
    }

    public function isValid() : bool {
        if (!$this->name)
            return false;
        if (!$this->no)
            return false;

        return true;
    }
}