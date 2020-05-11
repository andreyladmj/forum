<?php
/**
 * Created by PhpStorm.
 * User: Srivoknovskiy
 * Date: 6/7/2017
 * Time: 11:35 PM
 */

namespace App\Http\Controllers;


use App\Reply;

class FavoritesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Reply $reply)
    {
        $reply->favorite();

        return back();
    }

    public function destroy(Reply $reply)
    {
        $reply->unfavorite();
    }
}