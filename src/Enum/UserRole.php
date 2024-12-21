<?php 
namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'Admin';
    case BOUTIQUIER = 'Boutiquier';
    case CLIENT = 'Client';
}
