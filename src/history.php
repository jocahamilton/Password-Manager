<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();header("Location: ./");die();}
require_once("function/sqllink.php");
require_once("function/basic.php");
$link=sqllink();
if(!$link) {session_destroy();header("Location: ./");die();}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();header("Location: ./");die();}

//CHECK AGAIN, TO AVOID PASSWORD CHANGE IN ANOTHER BROWSER
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();header("Location: ./");die();}
echoheader();
?>
<div class="container theme-showcase" style="margin-top:-30px;">
    <div class="page-header">
        <h1>Trusted Devices</h1>
    </div>
    <table class="table">
    <tr><th>Device Type</th><th>Set Time</th><th>Untrust (Disable PIN)</th></tr>
    <?php
        $sql="SELECT * FROM `pin` WHERE `userid`= ?";
        $res=sqlexec($sql,array($id),$link);
		while ($i = $res->fetch(PDO::FETCH_ASSOC)){ 
            $did=$i['device'];
            $ctime=(int)$i['createtime'];
            $ua=$i['ua'];
            echo "<tr><td class='uacell'>".$ua."</td><td class='timestampcell'>".$ctime."</td><td><a href='javascript: unsetpin(\"".$did."\")'>Untrust this device</a></td></tr>";
		}
    ?>
    </table>
    <div class="page-header">
        <h1>Login History</h1>
    </div>
    <p>Red entries indicate password error (i.e. error try)</p>
    <table class="table">
    <tr><th>Device Type</th><th>Login IP</th><th>Login Time</th></tr>
    <?php
        $sql="SELECT * FROM `history` WHERE `userid`= ? LIMIT 60";
        $res=sqlexec($sql,array($id),$link);
		while ($i = $res->fetch(PDO::FETCH_ASSOC)){ 
            $ip=$i['ip'];
            $ua=$i['ua'];
            $ctime=(int)$i['time'];
            if((int)$i['outcome']==0)
                $color=' style="color:red"';
            else
                $color='';
            echo "<tr".$color."><td class='uacell'>".$ua."</td><td>".$ip."<td class='timestampcell'>".$ctime."</td></tr>";
		}
    ?>
    </table>   
</div>
<script type="text/javascript">
function unsetpin(devicex)
{
    $.post("deletepin.php",{user:"<?php echo $usr;?>",device:devicex},function(msg){location.reload(true);});
}
</script>
<?php echofooter();?>