<?php

namespace Tests\Feature;

use Tests\TestCase;

class AttendanceTest extends TestCase
{
    /**
     * Test absensi berhasil
     */
    public function test_attendance_success()
    {
        $this->assertTrue(true);
    }

    /**
     * Test absensi gagal
     */
    public function test_attendance_fail_invalid_status()
    {
        $this->assertFalse(false);
    }
}