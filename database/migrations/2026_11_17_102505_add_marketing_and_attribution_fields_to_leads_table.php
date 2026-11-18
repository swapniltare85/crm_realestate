<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Project & Sub-project incoming from web
            $table->string('project_name')->nullable()->after('project_id');
            $table->string('sub_project_name')->nullable()->after('project_name');
            $table->text('description')->nullable()->after('notes');

            // Contact & Geo / IP metadata
            $table->string('phone_country_code', 10)->nullable()->after('phone');
            $table->string('ip_country_code', 10)->nullable()->after('city');
            $table->string('ip_address', 45)->nullable()->after('ip_country_code');
            $table->string('physical_location')->nullable()->after('preferred_location');

            // Google Click ID & Tracking URLs
            $table->string('gclick_id')->nullable()->after('ip_address');
            $table->text('url')->nullable()->after('gclick_id');
            $table->string('device', 50)->nullable()->after('url');
            $table->string('platform', 100)->nullable()->after('device');

            // UTM Parameters
            $table->string('utm_source', 100)->nullable()->after('platform');
            $table->string('utm_medium', 100)->nullable()->after('utm_source');
            $table->string('utm_campaign', 255)->nullable()->after('utm_medium');
            $table->string('utm_term', 255)->nullable()->after('utm_campaign');
            $table->string('utm_content', 255)->nullable()->after('utm_term');

            // Digital Ads & Campaign Tracking Details
            $table->string('source_key', 100)->nullable()->after('utm_content');
            $table->string('sub_source', 100)->nullable()->after('source_key');
            $table->string('campaign_id', 100)->nullable()->after('sub_source');
            $table->string('campaign_name', 255)->nullable()->after('campaign_id');
            $table->string('ads_id', 100)->nullable()->after('campaign_name');
            $table->string('ads_name', 255)->nullable()->after('ads_id');
            $table->string('ads_group_id', 100)->nullable()->after('ads_name');
            $table->string('ads_set_name', 255)->nullable()->after('ads_group_id');
            $table->string('ads_extension_id', 100)->nullable()->after('ads_set_name');
            $table->string('ads_placement', 100)->nullable()->after('ads_extension_id');
            $table->string('ads_position', 100)->nullable()->after('ads_placement');
            $table->string('ads_keyword', 255)->nullable()->after('ads_position');
            $table->string('keyword_type', 100)->nullable()->after('ads_keyword');
            $table->string('ad_strategy', 100)->nullable()->after('keyword_type');

            // Pricing & Organic flag
            $table->decimal('sourcing_price', 15, 2)->nullable()->after('ad_strategy');
            $table->boolean('is_organic')->default(false)->after('sourcing_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'project_name',
                'sub_project_name',
                'description',
                'phone_country_code',
                'ip_country_code',
                'ip_address',
                'physical_location',
                'gclick_id',
                'url',
                'device',
                'platform',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'source_key',
                'sub_source',
                'campaign_id',
                'campaign_name',
                'ads_id',
                'ads_name',
                'ads_group_id',
                'ads_set_name',
                'ads_extension_id',
                'ads_placement',
                'ads_position',
                'ads_keyword',
                'keyword_type',
                'ad_strategy',
                'sourcing_price',
                'is_organic',
            ]);
        });
    }
};
