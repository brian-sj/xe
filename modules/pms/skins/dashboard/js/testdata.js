		  var users_data2 = {
	"data":[
		{"id":1, "text":"기획", "start_date":"25-04-2015", "duration":"7", "progress": 0.6, "open": true, "users": ["John", "Mike", "Anna"], "priority": "2"},
		{"id":2, "text":"SRS", "start_date":"02-05-2015", "duration":"3", "parent":"1", "progress": 0.8, "open": true, "users": ["John", "Mike"], "priority": "1"},
		{"id":3, "text":"화면설계", "start_date":"03-05-2015", "duration":"3", "parent":"1", "progress": 0.5, "open": true, "users": ["Anna"], "priority": "1"},
		{"id":4, "text":"프로그램 설계", "start_date":"04-05-2015", "duration":"5", "parent":"1", "progress": 0.1, "open": true, "users": ["Mike", "Anna"], "priority": "2"},
		{"id":5, "text":"개발", "start_date":"09-05-2015", "duration":"10", "progress": 0.2, "open": true, "users": ["John"], "priority": "3"},
		{"id":6, "text":"침례보고서", "start_date":"11-05-2015", "duration":"4", "parent":"5", "progress": 0, "open": true, "users": ["John"], "priority": "2"},
		{"id":7, "text":"서무", "start_date":"12-05-2015", "duration":"6", "parent":"5", "progress": 1, "open": true, "users": ["Mike", "Anna"], "priority": "2"},
		{"id":8, "text":"행정", "start_date":"14-05-2015", "duration":"5", "parent":"5", "progress": 0.8, "open": true, "users": ["Anna"], "priority": "3"},
		{"id":9, "text":"로그인", "start_date":"09-05-2015", "duration":"2", "parent":"5", "progress": 0.2, "open": true, "users": ["Mike", "Anna"], "priority": "1"},
		{"id":10, "text":"세션 결합모듈", "start_date":"09-05-2015", "duration":"4", "parent":"5", "progress": 0, "open": true, "users": ["John", "Mike"], "priority": "1"},
		{"id":11, "text":"파일업로드", "start_date":"13-05-2015", "duration":"4", "parent":"5", "progress": 0.5, "open": true, "users": ["John", "Anna"], "priority": "3"},
		{"id":12, "text":"테스트", "start_date":"19-05-2015", "duration":"3", "progress": 0.1, "open": true, "users": ["John"], "priority": "3"},
		{"id":13, "text":"매뉴얼", "start_date":"20-05-2015", "duration":"3", "progress": 0, "open": true, "users": ["Anna"], "priority": "3"}
	],
	"links":[
		{"id":"1","source":"1","target":"2","type":"1"},
		{"id":"2","source":"1","target":"3","type":"1"},
		{"id":"3","source":"1","target":"4","type":"1"},

		{"id":"15","source":"13","target":"17","type":"1"},
		{"id":"16","source":"17","target":"18","type":"0"},
		{"id":"17","source":"18","target":"19","type":"0"},
		{"id":"18","source":"19","target":"20","type":"0"},
		{"id":"19","source":"15","target":"21","type":"2"},
		{"id":"20","source":"15","target":"22","type":"2"},
		{"id":"21","source":"15","target":"23","type":"2"}
	]
};