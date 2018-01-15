<?php

function create($class, $attributes = [], $times = null)
{
    return factory($class, $times)->create($attributes)->fresh();
}
function make($class, $attributes = [], $times = null)
{
    return factory($class, $times)->make($attributes);
}