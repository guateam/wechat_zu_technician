<?php
function getcheckinstatus($id)
{
    $attendance=get('attendance','job_number',$id);
    if($attendance)
	{
        if(time()-strtotime($attendance[count($attendance)-1]['check_time'])<24*60*60&&$attendance[count($attendance)-1]['sign_type']==0)
		{
            return 1;
        }
    }
    return 0;
}
?>