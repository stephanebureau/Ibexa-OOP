<?php
namespace App\Entity;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * interface for remote load balanced hosts
 */
interface HostInterface
{
    // return remote hosts load (from 0 to 1)
    public function getLoad(): float;

    // send Request to remote host and return its response
    public function handleRequest(Request $request): Response;
}