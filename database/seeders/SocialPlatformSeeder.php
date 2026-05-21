<?php

namespace Database\Seeders;

use App\Models\SocialPlatform;
use Illuminate\Database\Seeder;

class SocialPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            [
                'name'         => 'Facebook',
                'slug'         => 'facebook',
                'icon'         => 'facebook',
                'color'        => '#1877F2',
                'status'       => true,
                'scopes'       => ['pages_show_list', 'pages_read_engagement', 'pages_manage_posts', 'pages_manage_metadata', 'read_insights'],
                'capabilities' => ['pages', 'scheduling', 'insights', 'comments'],
            ],
            [
                'name'         => 'Instagram',
                'slug'         => 'instagram',
                'icon'         => 'instagram',
                'color'        => '#E1306C',
                'status'       => true,
                'scopes'       => ['instagram_basic', 'instagram_content_publish', 'instagram_manage_insights', 'instagram_manage_comments', 'pages_show_list'],
                'capabilities' => ['scheduling', 'insights', 'comments'],
            ],
            [
                'name'         => 'LinkedIn',
                'slug'         => 'linkedin',
                'icon'         => 'linkedin',
                'color'        => '#0A66C2',
                'status'       => true,
                'scopes'       => ['openid', 'profile', 'email', 'w_member_social', 'r_organization_social', 'rw_organization_admin'],
                'capabilities' => ['pages', 'scheduling', 'insights'],
            ],
            [
                'name'         => 'Twitter / X',
                'slug'         => 'twitter',
                'icon'         => 'twitter',
                'color'        => '#000000',
                'status'       => true,
                'scopes'       => ['tweet.read', 'tweet.write', 'tweet.moderate.write', 'users.read', 'offline.access'],
                'capabilities' => ['scheduling'],
            ],
            [
                'name'         => 'Pinterest',
                'slug'         => 'pinterest',
                'icon'         => 'pinterest',
                'color'        => '#E60023',
                'status'       => true,
                'scopes'       => ['boards:read', 'boards:write', 'pins:read', 'pins:write', 'user_accounts:read'],
                'capabilities' => ['boards', 'scheduling', 'insights'],
            ],
            [
                'name'         => 'YouTube',
                'slug'         => 'youtube',
                'icon'         => 'youtube',
                'color'        => '#FF0000',
                'status'       => true,
                'scopes'       => ['https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube.readonly'],
                'capabilities' => ['channels', 'scheduling', 'insights'],
            ],
            [
                'name'         => 'Threads',
                'slug'         => 'threads',
                'icon'         => 'threads',
                'color'        => '#000000',
                'status'       => true,
                'scopes'       => ['threads_basic', 'threads_content_publish', 'threads_manage_insights', 'threads_manage_replies'],
                'capabilities' => ['scheduling', 'insights'],
            ],
            [
                'name'         => 'TikTok',
                'slug'         => 'tiktok',
                'icon'         => 'tiktok',
                'color'        => '#010101',
                'status'       => true,
                'scopes'       => ['user.info.basic', 'video.list', 'video.upload', 'video.publish'],
                'capabilities' => ['scheduling'],
            ],
        ];

        foreach ($platforms as $platform) {
            SocialPlatform::updateOrCreate(
                ['slug' => $platform['slug']],
                $platform
            );
        }
    }
}
