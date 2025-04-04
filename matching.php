<?php
// Logger class: logs messages to a file and echoes them to the console.
class Logger {
    protected $logFile;

    public function __construct($filePath = 'match_process_log.txt') {
        $this->logFile = $filePath;
    }

    public function log($message) {
        $formattedMessage = date('Y-m-d H:i:s') . " " . $message . PHP_EOL;
        // Write to log file
        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
        // Output to console
        echo $formattedMessage;
    }
}

// Define a common RuleInterface.
interface RuleInterface {
    /**
     * Returns true if the lost report and the found report match based on the rule.
     */
    public function match(array $lostReport, array $foundReport);

    /**
     * Returns the name of the rule.
     */
    public function getRuleName();
}

// Rule to check if the item names are similar (using a simple case-insensitive substring match).
class ItemNameMatchRule implements RuleInterface {
    public function match(array $lostReport, array $foundReport) {
        // For example, check if lost item name is a substring of found item name (or vice versa)
        $lostName = strtolower($lostReport['item_name']);
        $foundName = strtolower($foundReport['item_name']);
        return (stripos($lostName, $foundName) !== false) || (stripos($foundName, $lostName) !== false);
    }

    public function getRuleName() {
        return "ItemNameMatchRule";
    }
}

// Rule to check if the locations match (simple case-insensitive equality or substring).
class LocationMatchRule implements RuleInterface {
    public function match(array $lostReport, array $foundReport) {
        $lostLocation = strtolower($lostReport['location']);
        $foundLocation = strtolower($foundReport['location']);
        // A simple check: if lost location is found within the found location string
        return stripos($foundLocation, $lostLocation) !== false;
    }

    public function getRuleName() {
        return "LocationMatchRule";
    }
}

// Combined rule: only returns true if ALL provided rules pass.
class CombinedMatchRule implements RuleInterface {
    protected $rules;

    public function __construct(array $rules) {
        $this->rules = $rules;
    }

    public function match(array $lostReport, array $foundReport) {
        foreach ($this->rules as $rule) {
            if (!$rule->match($lostReport, $foundReport)) {
                return false;
            }
        }
        return true;
    }

    public function getRuleName() {
        $names = array_map(function($rule) {
            return $rule->getRuleName();
        }, $this->rules);
        return "CombinedMatchRule(" . implode(", ", $names) . ")";
    }
}

// Matching engine that applies rules to a lost report against found reports.
class MatchingEngine {
    protected $rules; // Array of RuleInterface instances.
    protected $logger;

    public function __construct(array $rules = [], Logger $logger = null) {
        $this->rules = $rules;
        $this->logger = $logger ?: new Logger();
    }

    public function addRule(RuleInterface $rule) {
        $this->rules[] = $rule;
    }

    /**
     * Processes a lost report against an array of found reports.
     * For each found report, each rule is evaluated and logged.
     * If a combined set of rules match, it simulates creating a record in the Matches table.
     */
    public function processMatch(array $lostReport, array $foundReports) {
        foreach ($foundReports as $foundReport) {
            $matchedRules = [];
            foreach ($this->rules as $rule) {
                if ($rule->match($lostReport, $foundReport)) {
                    $matchedRules[] = $rule->getRuleName();
                    $this->logger->log("Rule triggered: " . $rule->getRuleName() . " for lost_report_id: " . $lostReport['lost_report_id'] . " with found_report_id: " . $foundReport['found_report_id']);
                } else {
                    $this->logger->log("Rule NOT triggered: " . $rule->getRuleName() . " for lost_report_id: " . $lostReport['lost_report_id'] . " with found_report_id: " . $foundReport['found_report_id']);
                }
            }
            // Example: if at least one rule (or a combined rule) matches, we consider it a candidate.
            if (!empty($matchedRules)) {
                // Simulate creating a new match record.
                $this->logger->log(">> Candidate match found for lost_report_id: " . $lostReport['lost_report_id'] . " with found_report_id: " . $foundReport['found_report_id'] . ". Matched rules: " . implode(", ", $matchedRules));
                // Here you would perform an INSERT into the Matches table.
            } else {
                $this->logger->log("No matching rules triggered for lost_report_id: " . $lostReport['lost_report_id'] . " with found_report_id: " . $foundReport['found_report_id']);
            }
        }
    }
}