package diff_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/diff"
)

func TestLines(t *testing.T) {
	tests := []struct {
		name   string
		before string
		after  string
		want   string
	}{
		{
			name:   "changed line",
			before: "<?php\nfunction greet($who) {}",
			after:  "<?php\nfunction greet(string $who) {}",
			want:   "src/A.php\n  2\n  - function greet($who) {}\n  + function greet(string $who) {}\n",
		},
		{
			name:   "inserted docblock keeps later lines aligned",
			before: "<?php\nfunction op($o) {}\nfunction tail($t) {}\n",
			after:  "<?php\n/**\n * @param 'eq'|'lt' $o\n */\nfunction op(string $o) {}\nfunction tail(int $t) {}\n",
			want: "src/A.php\n  2\n  - function op($o) {}\n  - function tail($t) {}\n" +
				"  + /**\n  +  * @param 'eq'|'lt' $o\n  +  */\n  + function op(string $o) {}\n  + function tail(int $t) {}\n",
		},
		{
			name:   "inserted lines only",
			before: "<?php\nfunction op(string $o) {}\n",
			after:  "<?php\n/**\n * @param 'eq'|'lt' $o\n */\nfunction op(string $o) {}\n",
			want:   "src/A.php\n  2\n  + /**\n  +  * @param 'eq'|'lt' $o\n  +  */\n",
		},
		{
			name:   "removed lines only",
			before: "<?php\n/**\n * @param mixed $o\n */\nfunction op($o) {}\nfunction tail($t) {}\n",
			after:  "<?php\nfunction op($o) {}\nfunction tail($t) {}\n",
			want:   "src/A.php\n  2\n  - /**\n  -  * @param mixed $o\n  -  */\n",
		},
		{
			name:   "separate changes report original line numbers",
			before: "<?php\n/**\n * @param mixed $o\n */\nfunction op($o) {}\nfunction tail($t) {}\n",
			after:  "<?php\nfunction op($o) {}\nfunction tail(int $t) {}\n",
			want: "src/A.php\n  2\n  - /**\n  -  * @param mixed $o\n  -  */\n" +
				"  6\n  - function tail($t) {}\n  + function tail(int $t) {}\n",
		},
		{
			name:   "no change",
			before: "<?php\nfunction greet($who) {}",
			after:  "<?php\nfunction greet($who) {}",
			want:   "",
		},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			got, changed := diff.Lines("src/A.php", test.before, test.after)
			if changed != (test.want != "") {
				t.Errorf("changed = %v, want %v", changed, test.want != "")
			}
			if got != test.want {
				t.Errorf("\n got: %q\nwant: %q", got, test.want)
			}
		})
	}
}
