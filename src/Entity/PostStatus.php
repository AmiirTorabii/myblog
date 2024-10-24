<?php

namespace App\Entity;

enum PostStatus : string {
    case Accept = 'accept';
    case Reject = 'reject';
    case PENDING ='pending';



    public static function fromString(string $status): self
    {
        return match ($status) {
            'pending' => self::PENDING,
            'accept' => self::Accept,
            'reject' => self::Reject,
            default => throw new \InvalidArgumentException("Invalid status: $status"),
        };
    }
    public static function fromEnum(PostStatus $status): self
    {
        return match ($status) {
             self::PENDING => 'pending' ,
             self::Accept => 'accept',
            self::Reject => 'reject',
            default => throw new \InvalidArgumentException("Invalid status: $status"),
        };
    }
}




?>