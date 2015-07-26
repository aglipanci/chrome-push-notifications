<div class="wrap">
    <?php 
        $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $query = parse_url($url, PHP_URL_QUERY);

    ?>
    <h2>Subscribers (<?php echo $total;  ?>)</h2>

    <table class="widefat">
    <thead>
        <tr>
            <th>ID</th>
            <th>Subscribtion ID</th>       
            <th>Registration Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th>ID</th>
            <th>Subscribtion ID</th>       
            <th>Registration Date</th>
            <th>Actions</th>
        </tr>
    </tfoot>
    <tbody>
        <?php
            foreach ($subscribers as $subscriber) {
                ?>
                <tr>
                    <th><?php echo $subscriber->id; ?></th>
                    <th><?php echo $subscriber->gcm_regid; ?></th>       
                    <th><?php echo $subscriber->created_at; ?></th>       
                    <?php 
                         if ($query) {
                            $url .= '&delete_subscriber=1';
                        } else {
                            $url .= '?delete_subscriber=1';
                        }
                    ?>
                    <th><a href="<?php echo $url; ?>">Delete</a></th>       
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