<?php namespace Arzynik\Task;
use Arzynik\Config\Github;
use Arzynik\Service\Github as Github2;
class Version {
    protected function getCurentVersion($repository) {
        $data = (new Github2())->send('/repos/' . $repository . '/releases/latest');
        if(!$data) {
            return [1,0,0];
        }
        $data = json_decode($data);
        if(!isset($data->tag_name)) {
            return [1,0,0];
        }
        $curVersion = explode('.',preg_replace('/[^\.0-9]/','',$data->tag_name));
        return [isset($curVersion[0])?$curVersion[0]:1,isset($curVersion[1])?$curVersion[1]:0,isset($curVersion[2])?$curVersion[2]:0];
    }
    protected function getChangeLevel($issue,$repository) {
        $data = (new Github2())->send('/repos/' . $repository . '/issues/' + $issue);
        if(!is_object($data)) {
            $data = json_decode($data);
        }
        $changed = 0;
        $tags = ['main' => Github::get()->getMainTags($repository),'feature' => Github::get()->getFeatureTags($repository),'bug' => Github::get()->getBugTags($repository)];
        foreach($data->labels as $label) {
            if(in_array($label->name,$tags['main'])) {
                return 3;
            }
            if($changed < 2 && in_array($label->name,$tags['feature'])) {
                $changed = 2;
            }
            if($changed < 1 && in_array($label->name,$tags['bug'])) {
                $changed = 1;
            }
        }
        return $changed;
    }
    protected function getChange($commits,$repository) {
        $changed = 0;
        $issues = [];
        foreach($commits as $commit) {
            preg_match_all('/(close(s|d)?|fix(es|ed)|resolve(d|s))\s+#([0-9]+)/i',$commit->message,$matches);
            if(is_array($matches) && isset($matches[5])) {
                foreach(array_unique($matches[5]) as $issue) {
                    $issues[$issue] = $issue;
                    if($changed < 3) {
                        $changed = max($changed,$this->getChangeLevel($issue,$repository));
                    }
                }
            }
        }
        return [$changed,$issues];
    }
    public function run($commits,$repository) {
        $version = $this->getCurentVersion($repository);
        list($change,$fixed) = $this->getChange($commits,$repository);
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
        return ['v' . implode('.',$version),$fixed];
    }
}