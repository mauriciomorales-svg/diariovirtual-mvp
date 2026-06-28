<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\ArticleAdminController;
use App\Models\Article;
use Illuminate\Http\Request;

/**
 * Controlador de desarrollo - mismas funciones que admin pero sin autenticación
 */
class ArticleDevController extends Controller
{
    protected ArticleAdminController $adminController;

    public function __construct(ArticleAdminController $adminController)
    {
        $this->adminController = $adminController;
    }

    public function index(Request $request)
    {
        return $this->adminController->index($request);
    }

    public function create()
    {
        return $this->adminController->create();
    }

    public function store(Request $request)
    {
        return $this->adminController->store($request);
    }

    public function edit(Article $article)
    {
        return $this->adminController->edit($article);
    }

    public function update(Request $request, Article $article)
    {
        return $this->adminController->update($request, $article);
    }

    public function editImage(Article $article)
    {
        return $this->adminController->editImage($article);
    }

    public function updateImage(Request $request, Article $article)
    {
        return $this->adminController->updateImage($request, $article);
    }

    public function extractFromSource(Article $article)
    {
        return $this->adminController->extractFromSource($article);
    }
}
