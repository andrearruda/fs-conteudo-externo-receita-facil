<?php
// Routes

$app->get('/[{amount}]', App\Action\RecipeAction::class);