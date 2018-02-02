<?php

namespace Interfaces;

interface TripItemInterface {

    public function saveData($profile_id, $body = array());

    public function updateData($body = array());

    public function generateResponse($trip_item, $body);

    public function generateClientResponse($tripItem, $body);

}
