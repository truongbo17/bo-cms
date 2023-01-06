<?php

namespace Bo\Blog\Providers;

use Bo\Base\Services\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this
            ->setDirPlugin("blog")
            ->setPrimaryKeyPlugin("blog")
            ->loadRoutes(["web"])
            ->loadHelper();
    }

    public function boot()
    {
        $this
            ->loadMigration()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews();

        \SideBarDashBoard::registerItem('blog')
            ->setLabel('Blog')
            ->setPosition(10)
            ->setRoute(bo_url('blog'))
            ->setIcon('nav-icon lar la-question-circle')
            ->render();
    }
}
