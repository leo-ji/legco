<style type="text/css">
    table{width: 100%;}
    td:nth-child(1){width: 45%;}
    td:nth-child(2){width: 45%;}
    td:nth-child(3){width: 10%; text-align: center}
    .final-result {text-align: center; font-size: 42px;}
    .result {text-align: right;}
    .votes, .meeting-results {font-size: 35px;}
    .yes, .passed{color: green;}
    .no, .negatived{color: red;}
    .abstain{color: grey;}
    .absent{color: black;}
    .chair{color: blue;}
    .present{color: purple;}
    .voting-history td:not(:first-child) {width: 10%; text-align: center; border-left: 1px solid black;}
</style>
<meta charset="UTF-8" />
<?php

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', true);

global $members_historical_votes;
$members_historical_votes = array();

function sort_votes($a, $b) {
    switch($a->vote){
        case "Yes":
            $a_value = 3;
            break;
        case "No":
            $a_value = 2;
            break;
        case "Abstain":
            $a_value = 1;
            break;
        case "Present":
            $a_value = 0;
        case "Absent":
            $a_value = -1;
            break;
        default:
            $a_value = -2;
    }
    switch($b->vote){
        case "Yes":
            $b_value = 3;
            break;
        case "No":
            $b_value = 2;
            break;
        case "Abstain":
            $b_value = 1;
            break;
        case "Present":
            $b_value = 0;
            break;
        case "Absent":
            $b_value = -1;
            break;
        default:
            $b_value = -2;
    }
    return $b_value - $a_value;
    
} 

usort($unsortedObjectArray, 'compare_weights');

function print_legco_results($url){

$legco_results = simplexml_load_file($url);

// var_dump($legco_results);
$passed = 0;
$negatived = 0;
$other_results = 0;
foreach($legco_results->meeting->vote as $vote){
    $date = $vote->{'vote-date'};
    $time = $vote->{'vote-time'};
    $motion['chinese'] = $vote->{'motion-ch'};
    $motion['english'] = $vote->{'motion-en'};
    $mover['chinese'] = $vote->{'mover-ch'};
    $mover['english'] = $vote->{'mover-en'};
    $mover['type'] = $vote->{'mover-type'};
    $vote_result = $vote->{'vote-summary'}->{'overall'}->{'result'};
    if($vote->{'vote-separate-mechanism'} == 'Yes'){
        $vote_separate = true;
        $vote_summary['functional']['result'] = $vote->{'vote-summary'}->{'functional-constituency'}->{'result'};
        $vote_summary['geographical']['result'] = $vote->{'vote-summary'}->{'geographical-constituency'}->{'result'};
    }else{
        $vote_separate = false;
    }
    
    if($vote_result == 'Passed'){
        $passed++;
    }elseif($vote_result == 'Negatived'){
        $negatived++;
    }else{
        $other_results++;
    }
        
        
    // var_dump($vote_summary);
    // var_dump($vote);
    
    print "<table>";
    print "<tr><th>" . $motion['chinese'] . "</th><th>" . $motion['english'] . "</th><th class='final-result " . strtolower($vote_result) . "' title='" . $vote_result . "'>&#9632</tr>";
    print "<tr><td>" . $date . " " . $time . "</td><td>" . $mover['chinese'] . " " . $mover['english'] . " (" . $mover['type'] . ")</td></tr>";
    if($vote_separate){
        $members_array = array();
        foreach($vote->{'individual-votes'}->member as $member){
            $members_array[] = $member;
        }
        $chair = $members_array[0];
        unset($members_array[0]);
        usort($members_array, 'sort_votes');
        array_unshift($members_array, $chair);
        print "<tr><td>Functional Constituency</td><td class='votes'>";
        foreach($members_array as $member){
            $attributes = $member->attributes();
            if($attributes['constituency'] == 'Functional'){
                print "<span class='" . strtolower($member->vote) . "' title='" . $attributes['name-ch'] . " " . $attributes ['name-en'] . "'>&#9679;</span>";
            }
        }
        print "</td><td>" . $vote_summary['functional']['result'] . "</tr>";
        
        print "<tr><td>Geographical Constituency</td><td class='votes'>";
        foreach($members_array as $member){
            $attributes = $member->attributes();
            if($attributes['constituency'] == 'Geographical'){
                print "<span class='" . strtolower($member->vote) . "' title='" . $attributes['name-ch'] . " " . $attributes ['name-en'] . "'>&#9679;</span>";
            }
        }
        print "</td><td>" . $vote_summary['geographical']['result'] . "</tr>";
    }else{
        $members_array = array();
        foreach($vote->{'individual-votes'}->member as $member){
            $members_array[] = $member;
        }
        $chair = $members_array[0];
        unset($members_array[0]);
        usort($members_array, 'sort_votes');
        array_unshift($members_array, $chair);
        print "<tr><td>Members</td><td class='votes'>";
        $counted_votes = 0;
        $printed = false;
        foreach($members_array as $member){
            $attributes = $member->attributes();
            if($counted_votes>=35 && !$printed){
                print "<br/>";
                $printed = true;
            }else{
                $counted_votes++;
            }
            print "<span class='" . strtolower($member->vote) . "' title='" . $attributes['name-ch'] . " " . $attributes ['name-en'] . "'>&#9679;</span>";
        }
        print "</td><td>" . $vote_summary['functional']['result'] . "</tr>";
    }
    
    print "</table><hr/>";
    
    /*print "<table>";
    print "<tr><th>Member</th><th>Constituency</th><th>Vote</th></tr>";
    */
    foreach($vote->{'individual-votes'}->member as $member){
        
        // print "<tr><td>";
        $attributes = $member->attributes();
        /*print $attributes['name-ch'] . " " . $attributes['name-en'];
        print "</td><td>" . $attributes['constituency'];
        print "</td><td class='" . strtolower($member->vote) . "'>";
        print "&#9679";
        print "</td></tr>";
        // print $attributes['name-en'];
        */
        $GLOBALS['members_historical_votes'][$attributes['name-ch'] . " " . $attributes['name-en']][strtolower($member->vote)] += 1;
    }
    // print "</table>";
    
}

    print "<h2>This meeting's results</h2>";
    print "<table>";
    print "<tr><th>Passed</th><th>Negatived</th><th>Other</th></tr>";
    print "<tr class='meeting-results'><td><span class='passed'>";
    
    $count_vote = 0;
    for($i=0;$i<$passed;$i++){
        $count_vote++;
        if($count_vote >= 40){
            print "<br/>";
            $count_vote = 1;
        }
        print "&#9632;";
    }
    print "</span></td><td><span class='negatived'>";
    $count_vote = 0;
    for($i=0;$i<$negatived;$i++){
        $count_vote++;
        if($count_vote >= 40){
            print "<br/>";
            $count_vote = 1;
        }
        print "&#9632;";
        
    }
    print "</td><td><span class='other-results'>";
    $count_vote = 0;
    for($i=0;$i<$other_results;$i++){
        $count_vote++;
        if($count_vote >= 40){
            print "<br/>";
            $count_vote = 1;
        }
        print "&#9632;";
    }
    print "</span></td></tr>";
    print "</table>";
    print "<hr/>";
}


    

print_legco_results("http://www.legco.gov.hk/yr13-14/chinese/counmtg/voting/cm_vote_20131023.xml");
print_legco_results("http://www.legco.gov.hk/yr13-14/chinese/counmtg/voting/cm_vote_20131016.xml");
print_legco_results("http://www.legco.gov.hk/yr13-14/chinese/counmtg/voting/cm_vote_20140709.xml");
print_legco_results("http://www.legco.gov.hk/yr13-14/chinese/counmtg/voting/cm_vote_20140521.xml");

print "<h1>Member's voting history</h1>";
print "<table class='voting-history'>";
print "<tr><th>Member</th><th>Present</th><th>Yes</th><th>No</th><th>Abstain</th><th>Absent</th></tr>";

foreach($members_historical_votes as $name=>$votes){
    print "<tr><td>" . $name ."</td><td>";
    print ($votes['yes']+$votes['no']+$votes['abstain']+$votes['present']);
    print "</td><td>" . $votes['yes'] . "</td><td>" . $votes['no'] . "</td><td>" . $votes['abstain'] . "</td><td>" . $votes['absent'] . "</td></tr>";
}

print "</table>";


?>