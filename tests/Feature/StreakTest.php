<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\Sector;
use App\Models\Streak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_consecutive_checkins_build_a_streak(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'DSA', 'slug' => 'dsa']);

        $this->createCheckin($user, $sector, Carbon::today()->subDays(2));
        $this->createCheckin($user, $sector, Carbon::today()->subDays(1));
        $this->createCheckin($user, $sector, Carbon::today());

        $streak = Streak::where('user_id', $user->id)->where('sector_id', $sector->id)->first();

        $this->assertSame(3, $streak->current_streak);
        $this->assertSame(3, $streak->longest_streak);
    }

    public function test_a_gap_breaks_the_current_streak_but_keeps_the_longest(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'DSA', 'slug' => 'dsa']);

        $this->createCheckin($user, $sector, Carbon::today()->subDays(10));
        $this->createCheckin($user, $sector, Carbon::today()->subDays(9));
        $this->createCheckin($user, $sector, Carbon::today()->subDays(8));
        // gap of several days
        $this->createCheckin($user, $sector, Carbon::today()->subDays(1));

        $streak = Streak::where('user_id', $user->id)->where('sector_id', $sector->id)->first();

        $this->assertSame(1, $streak->current_streak, 'current streak should only count the most recent run');
        $this->assertSame(3, $streak->longest_streak, 'longest streak should remember the earlier 3-day run');
    }

    public function test_streak_is_zero_when_last_checkin_is_older_than_yesterday(): void
    {
        $user = User::factory()->create();
        $sector = Sector::create(['name' => 'DSA', 'slug' => 'dsa']);

        $this->createCheckin($user, $sector, Carbon::today()->subDays(5));

        $streak = Streak::where('user_id', $user->id)->where('sector_id', $sector->id)->first();

        $this->assertSame(0, $streak->current_streak);
        $this->assertSame(1, $streak->longest_streak);
    }

    private function createCheckin(User $user, Sector $sector, Carbon $date): Checkin
    {
        return Checkin::create([
            'user_id' => $user->id,
            'date' => $date,
            'sector_id' => $sector->id,
            'minutes_spent' => 30,
            'checkin_type' => 'study',
        ]);
    }
}
