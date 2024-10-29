<?php
namespace com\icemalta\kahuna\api\model;

enum AccessLevel: string
{
    case Admin = "admin";
    case Client = "client";
}