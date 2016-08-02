import List from 'list.js';

export default function() {
	var table = 'projects-sorter';
	var userList = new List(table, {
		valueNames: [ 'project', 'maker', 'status', 'updated' ],
	});
}
