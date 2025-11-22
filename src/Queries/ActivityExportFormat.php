<?php

declare(strict_types=1);

namespace Sportlog\GarminConnect\Queries;

/**
 * Enum for activity export formats.
 */
enum ActivityExportFormat: string
{
    case FIT = 'fit';
    case TCX = 'tcx';
    case GPX = 'gpx';
    case CSV = 'csv';
    case KML = 'kml';
}
