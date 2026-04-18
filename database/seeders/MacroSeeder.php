<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Macro;

class MacroSeeder extends Seeder
{
    public function run(): void
    {
        Macro::create([
            'title' => 'Standard Greeting & Investigating',
            'content' => "Hello,\n\nThank you for reaching out. I am currently looking into this issue and will provide an update shortly. \n\nBest regards,\nIT Support",
        ]);

        Macro::create([
            'title' => 'Request: Restart Computer',
            'content' => "Hello,\n\nCould you please try completely restarting your computer (Start > Power > Restart) and let me know if the issue persists? Often, a fresh reboot resolves these types of glitches.\n\nThank you!",
        ]);

        Macro::create([
            'title' => 'Request: More Information',
            'content' => "Hi,\n\nIn order to troubleshoot this further, could you please provide a bit more detail? Specifically:\n- When did this start happening?\n- Are there any specific error messages on the screen?\n\nThanks!",
        ]);

        Macro::create([
            'title' => 'Closing: Inactivity',
            'content' => "Hello,\n\nSince we haven't heard back from you in a few days, I am going to go ahead and close this ticket. If you are still experiencing this issue, please feel free to reply or open a new ticket.\n\nBest regards,\nIT Support",
        ]);
    }
}
