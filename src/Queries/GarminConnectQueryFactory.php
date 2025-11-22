<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect\Queries;

/**
 * Factory for predefined Garmin Connect queries.
 */
class GarminConnectQueryFactory
{
    /**
     * Search activities with optional filters.
     * 
     * @param int $start The starting index for the search results.
     * @param int $limit The maximum number of results to return.
     * @param string|null $activityType Optional activity type filter.
     * @param string|null $sortOrder Optional sort order for the results.
     */
    public static function searchActivities(int $start = 0, int $limit = 10, ?string $activityType = null, ?string $sortOrder = null): GarminConnectQuery
    {
        return GarminConnectQuery::get(
            '/activitylist-service/activities/search/activities',
            [
                'start' => $start,
                'limit' => $limit,
                'activityType' => $activityType,
                'sortOrder' => $sortOrder,
            ]
        );
    }

    /**
     * Get details of a specific activity by its ID.
     * 
     * @param int $activityId The ID of the activity.
     */
    public static function getActivityDetails(int $activityId): GarminConnectQuery
    {
        return GarminConnectQuery::get("/activity-service/activity/{$activityId}");
    }

    /**
     * Get available activity types.
     */
    public static function getActivityTypes(): GarminConnectQuery
    {
        return GarminConnectQuery::get('/activity-service/activity/activityTypes');
    }

    /**
     * Export an activity in the specified format.
     * 
     * @param int $activityId The ID of the activity to export.
     * @param ActivityExportFormat $type The format to export the activity in. If not supplied, defaults to FIT.
     */
    public static function exportActivity(int $activityId, ActivityExportFormat $type = ActivityExportFormat::FIT): GarminConnectQuery
    {
        if ($type === ActivityExportFormat::FIT) {
            return GarminConnectQuery::get("/download-service/files/activity/{$activityId}");
        } else {
            return GarminConnectQuery::get("/download-service/export/{$type->value}/activity/{$activityId}");
        }
    }

    /**
     * Get user profile user settings.
     */
    public static function getUserProfileUserSettings(): GarminConnectQuery
    {
        return GarminConnectQuery::get('/userprofile-service/userprofile/user-settings');
    }

    /**
     * Get user profile settings.
     */
    public static function getUserProfileSettings(): GarminConnectQuery
    {
        return GarminConnectQuery::get('/userprofile-service/userprofile/settings');
    }

    /**
     * Get registered devices for the user.
     */
    public static function getRegisteredDevices(): GarminConnectQuery
    {
        return GarminConnectQuery::get('/device-service/deviceregistration/devices');
    }
}
