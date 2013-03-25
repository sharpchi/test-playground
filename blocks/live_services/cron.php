<?php

/*******************************************************************************
Copyright (C) 2009  Microsoft Corporation. All rights reserved.
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*******************************************************************************/

        global $CFG;
        require_once( $CFG->dirroot . '/blocks/live_services/services/alerts/message.class.php' );
        /* Run the alerts group message cron job */
        $message = new message();
        $message->run_cron_group_deliver();
        /* More cron jobs could be created here */
?>