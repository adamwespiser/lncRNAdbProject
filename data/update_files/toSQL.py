# toSQL.py

def main():	
	readPath = "/home/wespisea/databaseProject/data/"
	writePath = "/home/wespisea/databaseProject/data/update_files/"
	readFile = readPath + "GeneData.tab"
	writeFile = writePath + "GeneData.sql"
	GeneData_toSQL(readFile, writeFile)
	readFile = readPath + "TransData.tab"
	writeFile = writePath + "TransData.sql"
	TransData_toSQL(readFile, writeFile)
	readFile = readPath + "PCAData.tab"
	writeFile = writePath + "PCAData.sql"
	tableName = "PCAData"
	columns = range(0, 33)
	toSQL(readFile, writeFile, tableName, columns)


'''
 ' Converts a text file, row by row, into SQL commands for database updates. Uses .tab column headers as SQLITE column headers
 '
 ' @param readFile The name of the text file containing the desired data
 ' @param writeFile The name of the .sql file containing SQLite commands
 ' @param tableName The desired name of the database table
 ' @param columns An array of column numbers to be read
'''
def toSQL(readFile, writeFile, tableName, columns):
	r = open(readFile, 'r')
	w = open(writeFile, 'w')
	
	#Get column headers
	line = r.readline()
	header = line.split()

	#Get first line of data to determine data types for each column
	line = r.readline()
	data = line.split()
	types = []
	for x in xrange(0, len(columns)):
		types.append(getType(data[columns[x]]))	

	w.write("BEGIN TRANSACTION;\n")
	#Create instruction for table initialization
	tCreate = "CREATE TABLE " + tableName + "("
	#Create format for all table data
	tInsert = "INSERT INTO " + tableName + "("
	for x in xrange(0, len(columns)):
		if (x > 0):
			tCreate += ", "
			tInsert += ", "
		tCreate += "[" + header[columns[x]] + "] " + types[x]
		tInsert += "[" + header[columns[x]] + "]"
	tCreate += ");\n"
	tInsert += ") VALUES ("
	w.write(tCreate)

	#Create instructions for table data
        while line != '':
                temp_tInsert = tInsert[:]
		split = line.split()
		for x in range(0, len(columns)):
			if (x > 0):
				temp_tInsert += ", "
			if(types[x] == "text"):
				temp_tInsert += "'" + split[columns[x]] + "'"
			else:
				temp_tInsert += split[columns[x]]
		temp_tInsert += ");\n"
                w.write(temp_tInsert)
		line = r.readline()

        w.write("COMMIT;\n")


'''
 ' Helper method, returns the correct data type for the given datum 
 ' 
 ' @param A value of unknown type
 ' @return The Sqlite instruction string for a specific type
'''
def getType(value):
	try:
		float(value)
		return "double (18,6)"
	except (ValueError):
		return "text"



'''
 ' Specific toSQL method for the GeneData table, adds a special column for an alternate ID
 ' for each gene, formatted like ZL_[0-9].A0 and uses different column names from the text file
 '
 ' @param readFile The text file to read data from
 ' @param writeFile The .sql file to write data to
 '
'''
def GeneData_toSQL(readFile, writeFile):
	r = open(readFile, 'r')
	w = open(writeFile, 'w')
	
	#Create table GeneData

	line = r.readline()
	headerSplit = line.split()
	
	#Skip headers line
	line = r.readline()	
	w.write("BEGIN TRANSACTION;\n")

	#Create table initialization instruction
	tCreate = "CREATE TABLE GeneData(ID text, geneID text"
	#Create format for table data 
	tInsert = "INSERT INTO GeneData(ID, geneID"
	for x in range(1, 33):
		tCreate = tCreate + ", [" + headerSplit[x] + "] double (18, 6)"
		tInsert = tInsert + ", [" + headerSplit[x] + "]"
	tCreate += ");\n"
	tInsert += ") VALUES ("
	w.write(tCreate)
        index = 1
	
	#Create all data insertions
        while line != '':
                temp_tInsert = tInsert[:]
		split = line.split()
		temp_tInsert += "'ZL_" + str(index) + ".A0', '" + split[0] + "'"

		for x in range(1, 33):
			temp_tInsert += ", " + split[x]
		temp_tInsert += ");\n"
                w.write(temp_tInsert)
		index += 1 
		line = r.readline()

        w.write("COMMIT;\n")


'''
 ' Specific toSQL method for TransData, breaks the coordinates column into the three columns: chromosome, 
 ' low, and high, uses specific column numbers local only to the TransData.tab file, and uses different
 ' columns names than the text file.
 '
 ' @param readFile The text file to read data from
 ' @param writeFile The .sql file to write data to
 '
'''
def TransData_toSQL(readFile, writeFile):
	r = open(readFile, 'r')
	w = open(writeFile, 'w')
	
	#Create table TransData

	line = r.readline()
	headerSplit = line.split()
	
	#First line is headers
	line = r.readline()	
	w.write("BEGIN TRANSACTION;\n")
	
	#Create instruction for table initialization
	tCreate = "CREATE TABLE TransData(geneID text, transID text, chromosome text, low int, high int"
	#Create format for table data
	tInsert = "INSERT INTO TransData(geneID, transID, chromosome, low, high"
	for x in range(2, 43):
		if (x == 40 or x == 41):
			tCreate += ", [" + headerSplit[x] + "] text"
		else:
			tCreate += ", [" + headerSplit[x] + "] double (18, 6)"
		tInsert += ", [" + headerSplit[x] + "]"
	tCreate += ");\n"
	tInsert += ") VALUES ("
	w.write(tCreate)

	#Create all data insertions
        while line != '':
                temp_tInsert = tInsert[:]
		split = line.split()

		#Split coordinates line into chromosome, low, high
		coords = split[len(split) - 8]
		chrom = coords.split(':')[0].strip('chr')
		start = coords.split(':')[1].split('-')[0]
		stop = coords.split(':')[1].split('-')[1]
		temp_tInsert += "'" + split[0] + "', '" + split[1] + "', '" + chrom + "', " + start + " , " + stop + ""
		
		#Read in other lines
		for x in range(2, 43):
			if(x == 40 or x == 41):
				temp_tInsert += ", '" + split[x] + "'"
			else:
				temp_tInsert += ", " + split[x]
		temp_tInsert += ");\n"
                w.write(temp_tInsert)
		line = r.readline()

        w.write("COMMIT;\n")


if __name__ == "__main__":
	main()
