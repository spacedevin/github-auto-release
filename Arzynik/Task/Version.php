<?php namespace Arzynik\Task;
use Arzynik\Config\Github;
use Arzynik\Service\Github as Github2;
class Version {
    protected function getCurentVersion() {
        $data = (new Github2())->send('/repos/' . $repository . '/releases/latest');
        $curVersion = explode('.',preg_replace('/[^\.0-9]/','',$data->tag_name));
        return [isset($curVersion[0])?$curVersion[0]:1,isset($curVersion[1])?$curVersion[1]:0,isset($curVersion[2])?$curVersion[2]:0];
    }
    protected function getChangeLevel($issue) {
        $data = (new Github2())->send('/repos/' . $repository . '/issues/' + $issue);
        $changed = 0;
        foreach($data->labels as $label) {
            if(in_array($label->name,Github::get()->getMainTags($repository))) {
                return 3;
            }
            if($changed < 2 && in_array($label->name,Github::get()->getFeatureTags($repository))) {
                $changed = 2;
            }
            if($changed < 1 && in_array($label->name,Github::get()->getBugTags($repository))) {
                $changed = 1;
            }
        }
        return $changed;
    }
    protected function getChange($commits) {
        $changed = 0;
        $issues = [];
        foreach($commits as $commit) {
            preg_match_all('/(close(s|d)?|fix(es|ed)|resolve(d|s))\s+#([0-9]+)/i',$commit->message);
            foreach(array_unique($matches[2]) as $issue) {
                $issues[$issue] = $issue;
                if($changed < 2) {
                    $changed = max($changed,$this->getChangeLevel($issue));
                }
            }
        }
        return [$changed,$issues];
    }
    public function run($commits) {
        $version = $this->getCurentVersion();
        list($change,$fixed) = $this->getChange($commits);
        if($change == 0) {
            $change = 3;
        }
        if($change == 3) {
            $version = [$version[0] + 1,0,0];
        } elseif($change == 2) {
            $version = [$version[0],$version[1] + 1,0];
        } elseif($change == 1) {
            $version = [$version[0],$version[1],$version[2] + 1];
        }
        return [$version,$fixed];
    }
}