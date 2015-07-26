<div class="wrap">

    <h2>Notifications (<?php echo $total;  ?>)</h2>

    <table class="widefat">
    <thead>
        <tr>
            <th>ID</th>
            <th>Notification Message</th>       
            <th>Hits</th>       
            <th>Sent Date</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th>ID</th>
            <th>Notification Message</th>       
            <th>Hits</th>       
            <th>Sent Date</th>
        </tr>
    </tfoot>
    <tbody>
        <?php
            foreach ($notifications as $notification) {
                ?>
                <tr>
                    <th><?php echo $notification->id; ?></th>
                    <th><?php echo $notification->notification; ?></th>       
                    <th><?php echo $notification->hits; ?></th>       
                    <th><?php echo $notification->created_at; ?></th>       
                </tr>
                <?php
            }
        ?>
    </tbody>
    </table>
     <?php 
        if ( $page_links ) {
            echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
        }
     ?>
</div>