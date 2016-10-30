array(1) {
    ["body"] => array(3) {
        ["from"] => int(0)["size"] => int(10)["query"] => array(1) {
            ["bool"] => array(4) {
                ["should"] => array(2) {
                    [0] => array(1) {
                        ["term"] => array(1) {
                            ["text"] => string(5)
                            "krant"
                        }
                    }[1] => array(1) {
                        ["term"] => array(1) {
                            ["text"] => string(3)
                            "kop"
                        }
                    }
                }["must"] => array(0) {}["must_not"] => array(0) {}["filter"] => array(1) {
                    ["or"] => array(1) {
                        ["filters"] => array(1) {
                            [0] => array(1) {
                                ["term"] => array(1) {
                                    ["date"] => string(9)
                                    "1969-07-5"
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}