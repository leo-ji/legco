<style type="text/css">
    table{width: 100%;}
    td:nth-child(1){width: 45%;}
    td:nth-child(2){width: 45%;}
    td:nth-child(3){width: 10%; text-align: center}
    .final-result {text-align: center; font-size: 42px;}
    .result {text-align: right;}
    .votes {font-size: 35px;}
    .yes, .passed{color: green;}
    .no, .negatived{color: red;}
    .abstain{color: grey;}
    .absent{color: black;}
    .chair{color: blue;}
    .voting-history td:not(:first-child) {width: 10%; text-align: center; border-left: 1px solid black;}
</style>

<?php

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', true);

global $members_historical_votes;
$members_historical_votes = array();

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
        $vote_summary['functional']['present'] = $vote->{'vote-summary'}->{'functional-constituency'}->{'present-count'};
        $vote_summary['functional']['yes'] = $vote->{'vote-summary'}->{'functional-constituency'}->{'yes-count'};
        $vote_summary['functional']['no'] = $vote->{'vote-summary'}->{'functional-constituency'}->{'no-count'};
        $vote_summary['functional']['abstain'] = $vote->{'vote-summary'}->{'functional-constituency'}->{'abstain-count'};
        $vote_summary['functional']['result'] = $vote->{'vote-summary'}->{'functional-constituency'}->{'result'};
        $vote_summary['geographical']['present'] = $vote->{'vote-summary'}->{'geographical-constituency'}->{'present-count'};
        $vote_summary['geographical']['yes'] = $vote->{'vote-summary'}->{'geographical-constituency'}->{'yes-count'};
        $vote_summary['geographical']['no'] = $vote->{'vote-summary'}->{'geographical-constituency'}->{'no-count'};
        $vote_summary['geographical']['abstain'] = $vote->{'vote-summary'}->{'geographical-constituency'}->{'abstain-count'};
        $vote_summary['geographical']['result'] = $vote->{'vote-summary'}->{'geographical-constituency'}->{'result'};
    }else{
        $vote_separate = false;
        $vote_summary['overall']['present'] = $vote->{'vote-summary'}->{'overall'}->{'present-count'};
        $vote_summary['overall']['yes'] = $vote->{'vote-summary'}->{'overall'}->{'yes-count'};
        $vote_summary['overall']['no'] = $vote->{'vote-summary'}->{'overall'}->{'no-count'};
        $vote_summary['overall']['abstain'] = $vote->{'vote-summary'}->{'overall'}->{'abstain-count'};
        $vote_summary['overall']['result'] = $vote->{'vote-summary'}->{'overall'}->{'result'};
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
    print "<tr><th>" . $motion['chinese'] . "</th><th>" . $motion['english'] . "</th><th class='final-result " . strtolower($vote_result) . "'>&#9632</tr>";
    print "<tr><td>" . $date . " " . $time . "</td><td>" . $mover['chinese'] . " " . $mover['english'] . " (" . $mover['type'] . ")</td></tr>";
    if($vote_separate){
        ob_start();
        print "<tr><td>Functional Constituency</td>" . "<td class='votes'><span class='yes'>";
        for($i = 0; $i < $vote_summary['functional']['yes']; $i++){
            print "&#9679";
        }
        print "</span><span class='no'>";
        for($i = 0; $i < $vote_summary['functional']['no']; $i++){
            print "&#9679";
        }
        print "</span><span class='abstain'>";
        for($i = 0; $i < $vote_summary['functional']['abstain']; $i++){
            print "&#9679";
        }
        $absent = 35 - $vote_summary['functional']['present'];
        print "</span><span class='absent'>";
        for($i = 0; $i < $absent; $i++){
            print "&#9679";
        }
        print "</span></td><td class='result'>" . $vote_summary['functional']['result'] . "</td></tr>";
        print "<tr><td>Geographical Constituency</td>" . "<td class='votes'>";
        
        print "</span><span class='chair'>&#9679";
        print "</span><span class='yes'>";
        for($i = 0; $i < $vote_summary['geographical']['yes']; $i++){
            print "&#9679";
        }
        print "</span><span class='no'>";
        for($i = 0; $i < $vote_summary['geographical']['no']; $i++){
            print "&#9679";
        }
        print "</span><span class='abstain'>";
        for($i = 0; $i < $vote_summary['geographical']['abstain']; $i++){
            print "&#9679";
        }
        $absent = 35 - $vote_summary['geographical']['present'];
        print "</span><span class='absent'>";
        for($i = 0; $i < $absent; $i++){
            print "&#9679";
        }
        print "</span></td><td class='result'>" . $vote_summary['functional']['result'] . "</td></tr>";
        ob_end_flush();
    }else{
        ob_start();
        print "<tr><td>Members</td>" . "<td class='votes'>";
        print "</span><span class='chair'>&#9679";
        print "</span><span class='yes'>";
        $current_votes = 1;
        $printed = true;
        for($i = 0; $i < $vote_summary['overall']['yes']; $i++){
            print "&#9679";
            $current_votes++;
            if($current_votes>34 && $printed){
                print "<br/>";
                $printed = false;
            }
        }
        print "</span><span class='no'>";
        for($i = 0; $i < $vote_summary['overall']['no']; $i++){
            print "&#9679";
            $current_votes++;
            if($current_votes>34 && $printed){
                print "<br/>";
                $printed = false;
            }
        }
        print "</span><span class='abstain'>";
        for($i = 0; $i < $vote_summary['overall']['abstain']; $i++){
            print "&#9679";
            $current_votes++;
            if($current_votes>34 && $printed){
                print "<br/>";
                $printed = false;
            }
        }
        $absent = 70 - $vote_summary['overall']['present'];
        print "</span><span class='absent'>";
        for($i = 0; $i < $absent; $i++){
            print "&#9679";
            $current_votes++;
            if($current_votes>34 && $printed){
                print "<br/>";
                $printed = false;
            }
        }
        print "</span></td><td class='result'>" . $vote_summary['overall']['result'] . "</td></tr>";
        ob_end_flush();
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
    print "<tr><td>" . $passed . "</td><td>" . $negatived . "</td><td>" . $other_results . "</td></tr>";
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
    if(isset($votes['present'])){
        print $votes['present'];
    }else{
    print ($votes['yes']+$votes['no']+$votes['abstain']);
    }
    print "</td><td>" . $votes['yes'] . "</td><td>" . $votes['no'] . "</td><td>" . $votes['abstain'] . "</td><td>" . $votes['absent'] . "</td></tr>";
}

print "</table>";


?>