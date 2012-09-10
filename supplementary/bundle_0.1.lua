--- get stat information on a path
-- @param path to stat
-- @param the ftype to return
-- @return false if ftype or path does not exist
function file_info(path, ftype)
    local attr = lighty.stat(path)
    if attr and attr[ftype] then
        return attr[ftype]
    end
    return false
end

--- Wrapper for reading a full file into a string
-- @param filename Full path to the file
-- @return a string with the content of the file
function read_file(filename) 
    local content = ""
    if file_info(filename, "is_file") then
        local file = io.open(filename, "r")
        content = file:read("*a")
        io.close(file)
    end
    return content
end

--- Wrapper for writing content to a file
-- @param filename Full path to the destionation file
-- @param content The string to write
function write_cache(filename, content) 
    local cache_file = io.open(filename, "w")
    cache_file:write(content)
    io.close(cache_file)
end

local extensions = {"css","js"} -- Allowed concat extensions

--- Concat multiple files into one file 
-- @param lighty lighty global variable passed to the method
-- @param match The files that will be concat into a file
-- @param fileExtension Do !NOT! include the dot ( . )
function combine_files(lighty, files, fileExtension)
    require "md5" 
    
    --local charset       = "; charset=utf-8"
    local charset       = ""
    local prefix        = "cache-"
    local rootPath      = lighty.env["physical.doc-root"] .. "/"
    local concatRoot     = "/tmp/"
    local lastModified  = 0
    local firstpath = ""    
   
    for file in string.gmatch(files, "([^,]+)") do
	if string.match(file,".+\." .. fileExtension) then
	inc_path = string.match(lighty.env["physical.doc-root"],"([^,]+)/")


	if firstpath:len() == 0
        then
        	firstpath = string.match(file, "([^,]+/)")
        end
        if not string.match(file,"^" .. firstpath) then
        	file = firstpath .. file
        end
	local fullPath = inc_path .. file
        
	modTime = file_info(fullPath, "st_mtime")
        if type(modTime) == "number" then
            lastModified = math.max(lastModified, modTime)
        end            
	end
     end
     
     local hash = lastModified .. "-" .. md5.sumhexa(files)
     lighty.header["Etag"] = '"' .. hash ..'"'
     
    local cacheFile = prefix .. hash .. '.' .. fileExtension

    if not file_info(concatRoot .. cacheFile, "is_file") then
	-- print("Cache '" .. files .. "' not found, creating...")
        local content = ""
	local inc_path = ""
	local flist = ""
	-- print("DOCROOT: " .. lighty.env["physical.doc-root"])
	inc_path = string.match(lighty.env["physical.doc-root"],"([^,]+)/")

	if not file_info(inc_path, "is_dir") then
		return 0
	end
	
        for file in string.gmatch(files, "([^,]+)") do
		if string.match(file,".+\." .. fileExtension) then
		if not string.match(file,"^" .. firstpath) then
			file = firstpath .. file
		end
		-- print("Concatting '" .. inc_path .. file .. "'")
		flist = flist .. " " .. file
		if file_info(inc_path .. file, "is_file") then
            		content = content .. "\n\n"
            		content = content .. "/** Concatted File (Extension: " .. fileExtension .. "): " .. file .. " Last Modified: " .. os.date("%c", file_info(inc_path .. file, "st_mtime")) .. " " .. "File Size: " .. file_info(inc_path .. file, "st_size") .. "b **/\n"
            		content = content .. read_file(inc_path .. file)
       		end
		end
	end
	local head = "/** Bundle Created: " .. os.date("%c") .. " **/\n"
	head = head .. "/** Concatted Files: " .. flist .. " **/\n"
	content = head .. content

        write_cache(concatRoot .. cacheFile, content )
    end
    lighty.env["physical.path"] = concatRoot .. cacheFile
end

if (string.match(lighty.env["physical.rel-path"],","))
then

	if (not file_info(lighty.env["physical.path"], "is_file")) then

		for k,ext in pairs(extensions) do    
    			extn = string.match(lighty.env["physical.rel-path"], "(.*\." .. ext .. ")")
			if extn then
        			return combine_files(lighty, extn, ext)
    			end
    		end
	end
end
