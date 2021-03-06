@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Transacting Centre</div>

                    <div class="panel-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                    <?php
                        $user = \Illuminate\Support\Facades\Auth::user();
                        $username = $user['username'];
                        $userid = $user['id'];
                        $email = $user['email'];
                        $admin = $user['admin'];
                        $points_available = $user['points_available'];

                            if ($admin ==1 ) {
                                echo "<h5>You are logged in as admin!</h5>";
                            }
                            else {
                                echo "<h5>You are logged in as ". $username."!</h5>";
                                echo "<h5>You have ".$points_available." points available.</h5>";
                            }
                    ?>



                    <script>

                        function acceptBid( bidId ) {
                            window.location = "../../bids/"+bidId+"/accept";
                        }

                        function rejectBid( bidId ) {
                            window.location = "../../bids/"+bidId+"/reject";
                        }

                    </script>

                    </div>

                    <hr>

                    <div class="panel-heading">
                        <h4>Posts Being Bid</h4>
                    </div>


                    <div class="panel-body">

                        <?php
                        $db = new mysqli('localhost', 'root', 'admin', 'blog');
                        if($db->connect_errno > 0){
                            die('Unable to connect to database [' . $db->connect_error . ']');
                        }
                        if ($admin == 0) {
                        $sql = "SELECT * from posts p, items i where p.item=i.itemid and i.owner = '".$email."';";}
                        else {
                            $sql = "SELECT * from posts p, items i where p.item=i.itemid;";
                        }

                        $posts_owned = $db->query($sql);
                        $index = 1;

                        while($row = $posts_owned->fetch_assoc()){
                            $current_post_id = $row['postid'];
                            $current_title = $row['title'];

                            $sql_inner = "SELECT * from bids b where b.post = ".$current_post_id.";";
                            $related_bids = $db->query($sql_inner);
                            //print_r($related_bids);
                            if ($related_bids->num_rows == 0)
                                {
                                    continue;
                                }

                            echo "<strong>".$index++.".</strong> ";
                            echo "<strong>Post Title:". $current_title ."</strong><br/>";

                            while($row_inner = $related_bids->fetch_assoc()){

                                $points = $row_inner['points'];
                                $bidder = $row_inner['bidder'];
                                $status = $row_inner['status'];
                                if ($status == 'SUCCESS'){
                                    continue;
                                }
                                $current_bid_id = $row_inner['bidid'];

                                echo "Bidder:". $bidder."<br>";
                                echo "Points:". $points;echo"<br>";
                                echo "
                                <div class='form-group'>
                                    <div class='col-md-8 col-md-offset-4'>
                                        <button type='submit' class='btn btn-primary' onclick='acceptBid(".$current_bid_id.")'>
                                        Accept
                                        </button>
                                        <button type='submit' class='btn btn-primary' onclick='rejectBid(".$current_bid_id.")'>
                                        Reject
                                        </button>
                                    </div>
                                </div>";

                            }

                            $related_bids->close();

                            echo"<br>";

                        }
                        $posts_owned->close();
                        ?>

                    </div>

                    <div class="panel-heading">
                        <h4>Posts Lending</h4>
                    </div>

                    <div class="panel-body">

                        <?php
                        $db = new mysqli('localhost', 'root', 'admin', 'blog');
                        if($db->connect_errno > 0){
                            die('Unable to connect to database [' . $db->connect_error . ']');
                        }
                        //$sql = "SELECT u1.username as owner, u2.username as bidder, b.points, b.updated_at as time
                        //FROM users u1, users u2, bids b, items i, posts p
                        //WHERE b.bidder = u2.email AND b.post = p.postid AND p.item = i.itemid AND i.owner = u1.email
                        //AND (b.bidder = '".$email."' OR i.owner = '".$email."') AND b.status = 'SUCCESS'
                        //ORDER BY time desc;";
                        //print($sql);

                        $sql = "SELECT u.username as bidder, p.title as title, b.points as points, l.created_at as time from users u, bids b, loans l, posts p, items i
                                WHERE u.email = b.bidder and b.bidid = l.bid and l.post = p.postid and p.item = i.itemid
                                and i.owner = '".$email."' and l.status = 'USING';";
                        if ($admin == 1) {
                            $sql = "SELECT u.username as bidder, p.title as title, b.points as points, l.created_at as time from users u, bids b, loans l, posts p, items i
                                WHERE u.email = b.bidder and b.bidid = l.bid and l.post = p.postid and p.item = i.itemid
                                and l.status = 'USING';";
                        }
                        //print($sql);
                        $index  = 1;
                        $related_history = $db -> query($sql);
                        while($row = $related_history->fetch_assoc()){

                            //$owner = $row['owner'];
                            $title = $row['title'];
                            $bidder = $row['bidder'];
                            $points_bided = $row['points'];
                            $time = $row['time'];

                            echo $index;echo"<br>";
                            echo "Title:". $title ."<br>";
                            echo "Bidder:". $bidder ."<br>";
                            echo "Points:". $points_bided ."<br>";
                            echo "Time:". $time ."<br><br><br>";

                            $index++;
                        }
                         


                        ?>
                    </div>

                    <div class="panel-heading">
                        <h4>Past Transactions</h4>
                    </div>


                    <div class="panel-body">

                        <?php
                        $db = new mysqli('localhost', 'root', 'admin', 'blog');
                        if($db->connect_errno > 0){
                            die('Unable to connect to database [' . $db->connect_error . ']');
                        }
                        $sql = "SELECT u1.username as owner, u2.username as bidder, b.points, b.updated_at as time
                        FROM users u1, users u2, bids b, items i, posts p, loans l
                        WHERE b.bidder = u2.email AND b.post = p.postid AND p.item = i.itemid AND i.owner = u1.email
                        AND (b.bidder = '".$email."' OR i.owner = '".$email."') AND b.status = 'SUCCESS' AND l.bid = b.bidid
                        AND l.status = 'RETURNED' ORDER BY time desc;";

                        if ($admin == 1) {
                            $sql = "SELECT u1.username as owner, u2.username as bidder, b.points, b.updated_at as time
                        FROM users u1, users u2, bids b, items i, posts p, loans l
                        WHERE b.bidder = u2.email AND b.post = p.postid AND p.item = i.itemid AND i.owner = u1.email
                        AND b.status = 'SUCCESS' AND l.bid = b.bidid
                        AND l.status = 'RETURNED' ORDER BY time desc;";
                        }
                        //print($sql);
                            $index  = 1;

                        $related_history = $db -> query($sql);
                        while($row = $related_history->fetch_assoc()){

                            $owner = $row['owner'];
                            //$title = $row['title'];
                            $bidder = $row['bidder'];
                            $points_bided = $row['points'];
                            $time = $row['time'];
                            echo $index;echo"<br>";
                            echo "Owner:". $owner ."<br>";
                            echo "Bidder:". $bidder ."<br>";
                            echo "Points:". $points_bided ."<br>";
                            echo "Time:". $time ."<br><br><br>";
                            $index++;
                        }



                        ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection