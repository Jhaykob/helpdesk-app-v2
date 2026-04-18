<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        Article::create([
            'title' => 'How to reset your VPN password',
            'content' => 'If you are locked out of the VPN, please navigate to the Okta self-service portal at okta.company.com. Click on "Forgot Password" and follow the SMS verification steps. Do not share your temporary pin with anyone.',
        ]);

        Article::create([
            'title' => 'Fixing Printer Connection Issues',
            'content' => 'Ensure you are connected to the "Corp-Secure" Wi-Fi network. Printers will not connect over the "Corp-Guest" network. Once verified, restart your print spooler service by typing "services.msc" in your Windows search bar.',
        ]);

        Article::create([
            'title' => 'Requesting a new Monitor or Hardware',
            'content' => 'All hardware requests must be approved by your direct line manager. Please ensure you attach written approval (or a screenshot of an email) to your ticket before submitting, or the request will be automatically denied.',
        ]);
    }
}
